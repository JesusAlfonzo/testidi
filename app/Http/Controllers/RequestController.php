<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest; // Modelo de la Solicitud (Cabecera)
use App\Models\Product;
use App\Models\Kit;
use App\Models\RequestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // Clase estándar para manejar peticiones HTTP
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
        $requests = InventoryRequest::with(['requester', 'approver', 'items.product'])
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        return view('admin.requests.index', compact('requests'));
    }

    public function create()
    {
        // Cargar productos y kits activos para el selector
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'stock']);
        $kits = Kit::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.requests.create', compact('products', 'kits'));
    }

    // Lógica: Guardar la solicitud y sus items
    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:255',
            'justification' => 'required|string|max:500',
            'destination_area' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,kit',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.kit_id' => 'nullable|exists:kits,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Validación lógica adicional: Que el ID corresponda al tipo
        foreach ($request->items as $item) {
            if ($item['item_type'] == 'product' && empty($item['product_id'])) {
                return back()->withInput()->withErrors(['items' => 'Debe seleccionar un producto válido.']);
            }
            if ($item['item_type'] == 'kit' && empty($item['kit_id'])) {
                return back()->withInput()->withErrors(['items' => 'Debe seleccionar un kit válido.']);
            }
        }

        DB::beginTransaction();
        try {
            // 1. Crear la cabecera (InventoryRequest)
            $solicitud = InventoryRequest::create([
                'requester_id' => auth()->id(),
                'reference' => $request->reference,
                'status' => 'Pending',
                'justification' => $request->justification,
                'destination_area' => $request->destination_area,
                'requested_at' => now(),
            ]);

            // 2. Preparar los ítems
            // Cargamos productos y kits en memoria para obtener precios eficientemente (evita N+1)
            $allProducts = Product::all()->keyBy('id');
            $allKits = Kit::all()->keyBy('id');

            $requestItemsData = [];

            foreach ($request->items as $item) {
                $price = 0;

                if ($item['item_type'] == 'product') {
                    $product = $allProducts->get($item['product_id']);
                    $price = $product->unit_price ?? 0; // O $product->cost, según tu lógica
                } elseif ($item['item_type'] == 'kit') {
                    $kit = $allKits->get($item['kit_id']);
                    $price = $kit->unit_price ?? 0;
                }

                $requestItemsData[] = [
                    // createMany se encargará de poner el 'request_id'
                    'product_id' => $item['item_type'] == 'product' ? $item['product_id'] : null,
                    'kit_id' => $item['item_type'] == 'kit' ? $item['kit_id'] : null,
                    'item_type' => $item['item_type'],
                    'quantity_requested' => $item['quantity'],
                    'unit_price_at_request' => $price,
                ];
            }

            // 3. Guardar los ítems usando la relación
            // createMany maneja automáticamente las claves foráneas y timestamps
            $solicitud->items()->createMany($requestItemsData);

            DB::commit();
            return redirect()->route('admin.requests.index')->with('success', 'Solicitud enviada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    // Muestra los detalles de la solicitud
    public function show(InventoryRequest $request)
    {
        $request->load([
            'requester',
            'approver',
            'items.product.unit',
            'items.kit.components.unit', // Cargar componentes para ver el detalle de kits
        ]);

        return view('admin.requests.show', compact('request'));
    }

    // Método para APROBAR o RECHAZAR una solicitud
    public function process(Request $httpRequest, InventoryRequest $request)
    {
        if ($request->status !== 'Pending') {
            return redirect()->back()->with('error', 'Esta solicitud ya fue procesada.');
        }

        $action = $httpRequest->input('action');
        $reason = $httpRequest->input('rejection_reason');

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $request->status = 'Approved';
                $request->approver_id = auth()->id();
                $request->processed_at = Carbon::now();

                // Cargar relaciones necesarias para el descuento de stock
                $request->load('items.product', 'items.kit.components');

                foreach ($request->items as $item) {
                    if ($item->item_type === 'product') {
                        // --- Lógica para PRODUCTO SIMPLE ---
                        $product = Product::lockForUpdate()->find($item->product_id);

                        if (!$product || $product->stock < $item->quantity_requested) {
                            throw new \Exception('Stock insuficiente para el producto: ' . ($product->name ?? 'Desconocido'));
                        }

                        $product->stock -= $item->quantity_requested;
                        $product->save();

                    } elseif ($item->item_type === 'kit') {
                        // --- Lógica para KIT (Descontar componentes) ---
                        $kit = $item->kit;
                        $qtyKit = $item->quantity_requested;

                        if (!$kit) {
                            throw new \Exception("Kit ID {$item->kit_id} no encontrado.");
                        }

                        foreach ($kit->components as $component) {
                            // Cantidad total a descontar = Cantidad de Kits * Cantidad de ese componente por Kit
                            $totalConsumption = $qtyKit * $component->pivot->quantity_required;

                            $prodComponent = Product::lockForUpdate()->find($component->id);

                            if (!$prodComponent || $prodComponent->stock < $totalConsumption) {
                                throw new \Exception("Stock insuficiente para componente '{$component->name}' del Kit '{$kit->name}'.");
                            }

                            $prodComponent->stock -= $totalConsumption;
                            $prodComponent->save();
                        }
                    }
                }

                $request->save();
                DB::commit();
                return redirect()->route('admin.requests.index')->with('success', 'Solicitud APROBADA y stock actualizado.');

            } elseif ($action === 'reject') {
                $request->status = 'Rejected';
                $request->approver_id = auth()->id();
                $request->processed_at = Carbon::now();
                $request->rejection_reason = $reason;
                $request->save();

                DB::commit();
                return redirect()->route('admin.requests.index')->with('warning', 'Solicitud RECHAZADA.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    // Métodos no implementados (bloqueados)
    public function destroy($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $r, $id) { return abort(404); }
}
