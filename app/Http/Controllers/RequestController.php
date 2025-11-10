<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as RequestModel; // 🔑 Alias para el Modelo de Solicitud (InventoryRequest)
use App\Models\Product;
use App\Models\Kit;
use App\Models\RequestItem;
use App\Http\Requests\StoreRequestRequest; // Asumo que esto se usa en algún lado, aunque no en store()
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request as HttpRequest; // 🔑 Alias para la clase base de Request de Laravel
use Carbon\Carbon;

class RequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:solicitudes_ver')->only('index', 'show');
        $this->middleware('permission:solicitudes_crear')->only('create', 'store');
        $this->middleware('permission:solicitudes_aprobar')->only('process');
    }

    public function index()
    {
        // Usamos RequestModel
        $requests = RequestModel::with(['requester', 'approver', 'items.product'])
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        return view('admin.requests.index', compact('requests'));
    }

    public function create()
    {
        // Cargar productos y kits activos para el selector
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'stock']);
        $kits = Kit::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        
        // Asume que tienes un modelo para Ubicaciones (Locations) si necesitas seleccionarlas
        // $locations = Location::all(); 

        return view('admin.requests.create', compact('products', 'kits')); // Pasa ambos a la vista
    }

    // Lógica: Guardar la solicitud y sus items
    // 🔑 CORREGIDO: Usar HttpRequest en lugar de Request
    public function store(HttpRequest $request)
    {
        $request->validate([
            'reference' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,kit',
            // En validación, el 'nullable' es crucial porque solo uno tendrá valor.
            'justification' => 'required|string|max:500', // <-- AGREGAR VALIDACIÓN
            'destination_area' => 'nullable|string|max:255', // <-- AGREGAR VALIDA
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.kit_id' => 'nullable|exists:kits,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Asegurar que si el tipo es 'product', product_id exista, y viceversa.
        foreach ($request->items as $item) {
            if ($item['item_type'] == 'product' && empty($item['product_id'])) {
                return back()->withInput()->withErrors(['items' => 'Debe seleccionar un producto para el tipo Producto.']);
            }
            if ($item['item_type'] == 'kit' && empty($item['kit_id'])) {
                return back()->withInput()->withErrors(['items' => 'Debe seleccionar un kit para el tipo Kit.']);
            }
        }

        // 🔑 CORREGIDO: Usar RequestModel::create en lugar de InventoryRequest::create
        $solicitud = RequestModel::create([
            'requester_id' => auth()->id(),
            'reference' => $request->reference,
            'status' => 'Pending',
            'justification' => $request->justification, // <-- AGREGADO
            'destination_area' => $request->destination_area, // <-- AGREGADO
            'requested_at' => now(),
        ]);

        $requestItems = [];
        foreach ($request->items as $item) {
            $requestItems[] = [
                // Usa el ID correcto según el tipo
                'product_id' => $item['item_type'] == 'product' ? $item['product_id'] : null, 
                'kit_id' => $item['item_type'] == 'kit' ? $item['kit_id'] : null, 
                'item_type' => $item['item_type'],
                'quantity_requested' => $item['quantity'],
                'created_at' => now(), 
                'updated_at' => now(), 
            ];
        }

        $solicitud->items()->insert($requestItems);

        return redirect()->route('admin.requests.index')->with('success', 'Solicitud enviada exitosamente.');
    }

    // Muestra los detalles de la solicitud
    public function show(RequestModel $request)
    {
        $request->load([
            'requester',
            'approver',
            'items.product.unit',
            // 🔑 NUEVO: Cargar los componentes del kit para la vista 'show'
            'items.kit.components.unit', 
        ]);

        return view('admin.requests.show', compact('request'));
    }

    // Método especializado para APROBAR o RECHAZAR una solicitud (Process)
    // 🔑 IMPORTANTE: Hemos añadido la lógica de descuento de STOCK para Kits aquí.
    public function process(HttpRequest $httpRequest, RequestModel $request)
    {
        if (!$request || $request->status !== 'Pending') {
            return redirect()->back()->with('error', '❌ Esta solicitud ya fue procesada o no existe.');
        }

        $action = $httpRequest->input('action');
        $reason = $httpRequest->input('rejection_reason');

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $request->status = 'Approved';
                $request->approver_id = auth()->id();
                $request->processed_at = Carbon::now();

                // Cargar items, productos y kits con sus componentes
                $request->load('items.product', 'items.kit.components');

                foreach ($request->items as $item) {
                    if ($item->item_type === 'product') {
                        // Lógica de Producto simple (existente)
                        $product = Product::lockForUpdate()->find($item->product_id);

                        if (!$product || $product->stock < $item->quantity_requested) {
                            DB::rollBack();
                            $productName = $product->name ?? 'ID:' . $item->product_id;
                            return redirect()->route('admin.requests.index')
                                ->with('error', '⚠️ Stock insuficiente o producto no encontrado: ' . $productName . '. Solicitud no aprobada.');
                        }
                        $product->stock -= $item->quantity_requested;
                        $product->save();

                    } elseif ($item->item_type === 'kit') {
                        // 🔑 Lógica de Kit (NUEVO: Descuenta todos los componentes)
                        $kit = $item->kit; // Ya cargado con load('items.kit.components')
                        $kitQuantity = $item->quantity_requested;

                        if (!$kit) {
                            throw new \Exception("Error: Kit ID {$item->kit_id} no encontrado.");
                        }
                        
                        // Iterar sobre CADA componente del Kit
                        foreach ($kit->components as $component) {
                            $requiredQuantity = $component->pivot->quantity_required;
                            $totalConsumption = $kitQuantity * $requiredQuantity;

                            $product = Product::lockForUpdate()->find($component->id);

                            if (!$product || $product->stock < $totalConsumption) {
                                DB::rollBack();
                                $productName = $product->name ?? 'ID:' . $component->id;
                                throw new \Exception("Stock insuficiente para el componente '{$productName}' del Kit '{$kit->name}'.");
                            }
                            
                            $product->stock -= $totalConsumption;
                            $product->save();
                        }
                    }
                }

                $request->save();
                DB::commit();
                return redirect()->route('admin.requests.index')->with('success', '✅ Solicitud APROBADA y stock actualizado correctamente.');

            } elseif ($action === 'reject') {
                $request->status = 'Rejected';
                $request->approver_id = auth()->id();
                $request->processed_at = Carbon::now();
                $request->rejection_reason = $reason;
                $request->save();

                DB::commit();
                return redirect()->route('admin.requests.index')->with('warning', '🛑 Solicitud RECHAZADA.');
            } else {
                DB::rollback();
                return redirect()->back()->with('error', 'Acción no válida.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            // Retorna a la vista anterior, mostrando el mensaje de error de la excepción
            return redirect()->back()->with('error', '❌ Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    // Métodos no implementados
    public function destroy($id)
    {
        return abort(404, 'La solicitud no puede ser eliminada, solo rechazada.');
    }
    public function edit($id)
    {
        return abort(404);
    }
    public function update(HttpRequest $request, $id)
    {
        return abort(404);
    }
}