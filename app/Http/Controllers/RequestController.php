<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as RequestModel; // Alias del modelo
use App\Models\Product;
use App\Models\Kit;
use App\Models\RequestItem;
use App\Models\User; // 🔑 AGREGADO: Necesario para el filtro de solicitantes
use App\Http\Requests\StoreRequestRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request as HttpRequest;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:solicitudes_ver')->only('index', 'show');
        $this->middleware('permission:solicitudes_crear')->only('create', 'store');
        $this->middleware('permission:solicitudes_aprobar')->only('process');
    }

    public function index(HttpRequest $request)
    {
        // 1. Cargar lista de usuarios para el filtro (select)
        $requesters = User::pluck('name', 'id');

        // 2. Iniciar consulta
        $query = RequestModel::with(['requester', 'approver', 'items.product'])
            ->orderBy('requested_at', 'desc');

        // 3. Aplicar Filtros
        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        // 4. Obtener resultados (Usamos get() para DataTables client-side)
        $requests = $query->get();

        // 5. Pasar variables a la vista
        return view('admin.requests.index', compact('requests', 'requesters'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'stock']);
        $kits = Kit::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.requests.create', compact('products', 'kits'));
    }

    public function store(StoreRequestRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $requestModel = RequestModel::create([
                'requester_id' => auth()->id(),
                'status' => 'Pending',
                'justification' => $validatedData['justification'],
                'destination_area' => $validatedData['destination_area'] ?? null,
                'requested_at' => Carbon::now(),
            ]);

            $itemsToStore = [];
            
            // Precargar productos y kits para evitar N+1
            $productIds = collect($validatedData['items'])->where('item_type', 'product')->pluck('product_id')->filter()->toArray();
            $kitIds = collect($validatedData['items'])->where('item_type', 'kit')->pluck('kit_id')->filter()->toArray();
            
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $kits = Kit::whereIn('id', $kitIds)->get()->keyBy('id');

            foreach ($validatedData['items'] as $item) {
                $price = 0;
                if ($item['item_type'] == 'product' && isset($products[$item['product_id']])) {
                    $price = $products[$item['product_id']]->cost ?? 0;
                } elseif ($item['item_type'] == 'kit' && isset($kits[$item['kit_id']])) {
                    $price = $kits[$item['kit_id']]->unit_price ?? 0;
                }

                $itemsToStore[] = new RequestItem([
                    'product_id' => $item['item_type'] == 'product' ? $item['product_id'] : null,
                    'kit_id' => $item['item_type'] == 'kit' ? $item['kit_id'] : null,
                    'item_type' => $item['item_type'],
                    'quantity_requested' => $item['quantity'],
                    'unit_price_at_request' => $price,
                ]);
            }

            $requestModel->items()->saveMany($itemsToStore);

            DB::commit();
            return redirect()->route('admin.requests.show', $requestModel)
                ->with('success', '✅ Solicitud enviada para aprobación: REQ-' . $requestModel->id);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', '❌ Error al guardar la solicitud: ' . $e->getMessage());
        }
    }

    public function show(RequestModel $request)
    {
        $request->load([
            'requester',
            'approver',
            'items.product.unit',
            'items.kit.components.unit',
        ]);

        return view('admin.requests.show', compact('request'));
    }

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

                $request->load('items.product', 'items.kit.components');

                foreach ($request->items as $item) {
                    if ($item->item_type === 'product') {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if (!$product || $product->stock < $item->quantity_requested) {
                             throw new \Exception('Stock insuficiente para el producto: ' . ($product->name ?? 'Desconocido'));
                        }
                        $product->stock -= $item->quantity_requested;
                        $product->save();
                    } elseif ($item->item_type === 'kit') {
                        $kit = $item->kit;
                        $qty = $item->quantity_requested;
                        
                        if (!$kit) throw new \Exception("Kit ID {$item->kit_id} no encontrado.");

                        foreach ($kit->components as $comp) {
                            $total = $qty * $comp->pivot->quantity_required;
                            $prod = Product::lockForUpdate()->find($comp->id);
                            if (!$prod || $prod->stock < $total) {
                                throw new \Exception("Stock insuficiente para componente {$comp->name} de Kit {$kit->name}");
                            }
                            $prod->stock -= $total;
                            $prod->save();
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
            }
            
            return redirect()->back()->with('error', 'Acción no válida.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', '❌ Error al procesar: ' . $e->getMessage());
        }
    }

    public function destroy($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(HttpRequest $request, $id) { return abort(404); }
}