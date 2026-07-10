<?php

namespace App\Http\Controllers;

use App\Events\StockUpdated;
use App\Models\InventoryRequest as RequestModel;
use App\Models\Product;
use App\Models\Kit;
use App\Models\RequestItem;
use App\Models\User;
use App\Models\Dispatch;
use App\Http\Requests\StoreRequestRequest;
use App\Services\CacheService;
use App\Services\InventoryRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;
use PDF;
use Carbon\Carbon;

class RequestController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->middleware('can:solicitudes_aprobar')->only('process');
    }

    public function index(HttpRequest $request)
    {
        // Usar server-side si es AJAX o si hay parámetros de DataTables
        $isDataTables = $request->filled('draw') || $request->ajax();
        
        if ($isDataTables) {
            return $this->indexDataTables($request);
        }

        // 2. Cargar lista de departamentos (destination_area)
        $departments = RequestModel::whereNotNull('destination_area')
            ->where('destination_area', '!=', '')
            ->distinct()
            ->orderBy('destination_area')
            ->pluck('destination_area', 'destination_area');

        // Determinar cantidad por página
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        // 3. Iniciar consulta base
        $query = RequestModel::with(['requester', 'approver', 'items.product'])
            ->orderBy('requested_at', 'desc');

        // 4. Aplicar Filtros de Seguridad (Admin ve todas, usuarios normales solo las propias)
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->can('solicitudes_aprobar')) {
            $query->where('requester_id', $user->id);
        }

        // 5. Aplicar Filtros de Búsqueda
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('destination_area')) {
            $query->where('destination_area', $request->destination_area);
        }
        // 6. Obtener resultados con paginación
        if ($request->get('view_all') === 'true') {
            $requests = $query->paginate($perPage)->appends($request->except('page'));
        } else {
            $requests = $query->paginate($perPage)->appends($request->except('per_page'));
        }

        return view('admin.requests.index', compact('requests', 'departments', 'perPage'));
    }

    protected function indexDataTables(HttpRequest $request)
    {
        $user = auth()->user();
        
        $query = RequestModel::with(['requester', 'approver']);

        // Admin ve todas las solicitudes, usuarios normales solo las propias
        if (!$user->isSuperAdmin() && !$user->can('solicitudes_aprobar')) {
            $query->where('requester_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('destination_area')) {
            $query->where('destination_area', $request->destination_area);
        }
        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        
        // Siempre permitir ordenamiento
        $orderColumn = (int) $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        // Mapear índice de DataTables al campo de ordenamiento
        // 0: id, 1: requester, 2: destination_area, 3: justification, 4: status, 5: date, 6: approver, 7: processed
        $columnMap = [
            0 => 'id',              // id
            1 => 'requester',      // requester_id - usar join
            2 => 'destination_area', // destination_area
            3 => 'justification',   // justification
            4 => 'status',          // status
            5 => 'requested_at',   // date
            6 => 'approver',       // approver_id - usar join
            7 => 'processed_at',    // processed
        ];
        $orderCol = $columnMap[$orderColumn] ?? 'requested_at';

        // Ordenar por relaciones usando join
        try {
            if ($orderCol === 'requester') {
                $query->join('users as requesters', 'requests.requester_id', '=', 'requesters.id')
                      ->orderBy('requesters.name', $orderDir)
                      ->select('requests.*');
            } elseif ($orderCol === 'approver') {
                $query->leftJoin('users as approvers', 'requests.approver_id', '=', 'approvers.id')
                      ->orderBy('approvers.name', $orderDir)
                      ->select('requests.*');
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } catch (\Exception $e) {
            $query->orderBy('requested_at', 'desc');
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(justification) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereRaw('LOWER(destination_area) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereHas('requester', function($rq) use ($search) {
                      $rq->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")]);
                  });
            });
        }

        $totalRecords = RequestModel::count();
        $totalFiltered = $query->count();

        $requests = $query->offset($start)->limit($length)->get();

        $data = $requests->map(function ($item) {
            $statusLabel = '';
            $statusClass = 'secondary';
            
            if ($item->status === RequestModel::STATUS_PENDING) {
                $statusLabel = 'Pendiente';
                $statusClass = 'warning';
            } elseif ($item->status === RequestModel::STATUS_APPROVED) {
                $statusLabel = 'Aprobada';
                $statusClass = 'success';
            } elseif ($item->status === RequestModel::STATUS_PROCESSED) {
                $statusLabel = 'Procesada';
                $statusClass = 'success';
            } elseif ($item->status === RequestModel::STATUS_REJECTED) {
                $statusLabel = 'Rechazada';
                $statusClass = 'danger';
            } elseif ($item->status === RequestModel::STATUS_PARTIALLY_PROCESSED || $item->status === 'Partially Processed') {
                $statusLabel = 'Procesado parcialmente';
                $statusClass = 'info';
            } elseif ($item->status === RequestModel::STATUS_DRAFT) {
                $statusLabel = 'Borrador';
                $statusClass = 'secondary';
            } else {
                $statusLabel = $item->status;
            }
            
            $displayJustification = $item->justification;

            if (preg_match('/^\[(ALTA|MEDIA|BAJA)\]\s*(.*)$/i', $item->justification, $matches)) {
                $displayJustification = $matches[2];
            }
            
            return [
                'id' => 'REQ-' . $item->id,
                'date' => $item->requested_at->format('d/m/Y H:i'),
                'requester' => $item->requester->name ?? 'N/A',
                'destination_area' => $item->destination_area ?? 'N/A',
                'justification' => Str::limit($displayJustification, 50),
                'status' => '<span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span>',
                'approver' => $item->approver->name ?? '-',
                'processed' => $item->processed_at ? $item->processed_at->format('d/m/Y') : '-',
                'actions' => view('admin.requests.partials.actions', ['item' => $item])->render(),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        // Regla 1: Validar días permitidos (Martes y Miércoles) vs Bypass Granular
        $today = \Carbon\Carbon::now()->dayOfWeek;
        if (!in_array($today, [\Carbon\Carbon::TUESDAY, \Carbon\Carbon::WEDNESDAY]) && !$user->can('solicitudes_fuera_horario')) {
            return redirect()->route('admin.requests.index')
                ->with('error', 'El sistema solo permite crear solicitudes los días Martes y Miércoles.');
        }

        // Cargar todos los productos y kits activos
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'stock', 'type', 'is_kit', 'category_id']);
        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);
        
        return view('admin.requests.create', compact('products', 'categories'));
    }

    public function store(StoreRequestRequest $request)
    {
        $this->authorize('solicitudes_crear');
        
        $user = auth()->user();
        
        // Regla 1: Validar días permitidos (protección de endpoint)
        $today = \Carbon\Carbon::now()->dayOfWeek;
        if (!in_array($today, [\Carbon\Carbon::TUESDAY, \Carbon\Carbon::WEDNESDAY]) && !$user->can('solicitudes_fuera_horario')) {
            return redirect()->back()->withInput()->with('error', 'El sistema solo permite crear solicitudes los días Martes y Miércoles.');
        }

        $validatedData = $request->validated();

        // Regla 2: Token Semanal por Usuario (1 solicitud a la semana)
        if (!$user->can('solicitudes_sin_limite_semanal')) {
            $hasWeeklyRequest = RequestModel::where('requester_id', $user->id)
                ->where('requested_at', '>=', now()->startOfWeek())
                ->exists();

            if ($hasWeeklyRequest) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Has agotado tu solicitud de esta semana. El límite se reinicia el próximo lunes.');
            }
        }


        DB::beginTransaction();
        try {
            // 1. Crear la cabecera de la solicitud
            $justification = $validatedData['justification'];

            $requestModel = RequestModel::create([
                'requester_id' => auth()->id(),
                'status' => RequestModel::STATUS_PENDING,
                'justification' => $justification,
                'destination_area' => $validatedData['destination_area'] ?? null,
                'reference' => $validatedData['reference'] ?? null,
                'requested_at' => Carbon::now(),
            ]);

            // 2. OPTIMIZACIÓN N+1: Preparar datos en memoria para precios
            $itemsCollection = collect($validatedData['items']);
            
            $productIds = $itemsCollection->pluck('product_id')->filter()->toArray();
            $productsDict = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $itemsToStore = [];

            foreach ($validatedData['items'] as $item) {
                $productId = $item['product_id'];
                $prod = $productsDict->get($productId);
                $price = $prod ? $prod->cost : 0; 

                // Preparamos el array para guardar
                $itemsToStore[] = [
                    'product_id' => $productId,
                    'kit_id' => null,
                    'item_type' => 'product',
                    'quantity_requested' => $item['quantity'],
                    'unit_price_at_request' => $price,
                ];
            }

            // Guardado masivo usando la relación
            $requestModel->items()->createMany($itemsToStore);

            DB::commit();
            return redirect()->route('admin.requests.index')
                ->with('success', 'Solicitud creada correctamente: REQ-' . $requestModel->id);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al guardar solicitud: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al guardar la solicitud. Por favor, intente de nuevo.');
        }
    }

    // Muestra los detalles de la solicitud
    public function show(RequestModel $request)
    {
        $this->authorize('solicitudes_ver');
        
        $request->load([
            'requester',
            'approver',
            'items.product.unit',
            'items.product.components.unit',
            'dispatches.dispatcher',
        ]);

        $decomposableKits = [];
        if ($request->status === RequestModel::STATUS_PENDING) {
            foreach ($request->items as $item) {
                if ($item->item_type === 'product' && $item->product) {
                    $prod = $item->product;
                    if ($prod->stock < $item->quantity_requested) {
                        // Buscar kits compuestos que contienen a este componente
                        $kits = Product::with('components')
                            ->where('type', 'composite_kit')
                            ->whereHas('components', function ($query) use ($prod) {
                                $query->where('child_id', $prod->id);
                            })
                            ->get();

                        foreach ($kits as $kit) {
                            $kitBatches = ProductBatch::where('product_id', $kit->id)
                                ->where('quantity', '>', 0)
                                ->get();

                            if ($kitBatches->isNotEmpty()) {
                                $relation = $kit->components()->where('child_id', $prod->id)->first();
                                $qtyInKit = $relation ? $relation->pivot->quantity : 1;

                                $decomposableKits[$prod->id][] = [
                                    'kit' => $kit,
                                    'batches' => $kitBatches,
                                    'quantity_in_kit' => $qtyInKit,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return view('admin.requests.show', compact('request', 'decomposableKits'));
    }

    // Método especializado para APROBAR o RECHAZAR una solicitud
    public function process(HttpRequest $httpRequest, RequestModel $request)
    {
        if (!$request || $request->status !== RequestModel::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Esta solicitud ya fue procesada o no existe.');
        }

        $action = $httpRequest->input('action');
        $reason = $httpRequest->input('rejection_reason');

        $service = new InventoryRequestService();

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $items = $httpRequest->input('items');
                if (!$items || !is_array($items)) {
                    return redirect()->back()->with('error', 'Los ítems del despacho no son válidos.');
                }

                $errors = [];
                foreach ($items as $index => $itemData) {
                    $productId = $itemData['product_id'] ?? null;
                    $qtyRequested = (int) ($itemData['quantity_requested'] ?? 0);
                    $qtyDispatched = (int) ($itemData['quantity_dispatched'] ?? 0);
                    $status = $itemData['status'] ?? 'approved';

                    if (!$productId) {
                        $errors[] = "Falta el ID del producto en la línea " . ($index + 1);
                        continue;
                    }

                    $product = Product::find($productId);
                    if (!$product) {
                        $errors[] = "El producto con ID {$productId} no existe.";
                        continue;
                    }

                    if ($qtyDispatched < 0) {
                        $errors[] = "La cantidad despachada para {$product->name} no puede ser negativa.";
                    }

                    if ($qtyDispatched > $qtyRequested) {
                        $errors[] = "La cantidad despachada para {$product->name} ({$qtyDispatched}) no puede superar la cantidad solicitada ({$qtyRequested}).";
                    }

                    if ($status === 'rejected' && $qtyDispatched !== 0) {
                        $errors[] = "La cantidad despachada para {$product->name} debe ser 0 si el ítem es negado.";
                    }

                    if ($qtyDispatched > 0 && $product->stock < $qtyDispatched) {
                        $errors[] = "Stock insuficiente para {$product->name}. Stock actual: {$product->stock}, solicitado para despacho: {$qtyDispatched}.";
                    }
                }

                if (!empty($errors)) {
                    return redirect()->back()->withInput()->with('error', implode('<br>', $errors));
                }

                // Ejecutar el despacho
                $dispatch = $service->dispatch($request, [
                    'items' => $items,
                    'notes' => $httpRequest->input('notes')
                ]);

                DB::commit();
                return redirect()->route('admin.requests.index')->with('success', 'Despacho ' . $dispatch->dispatch_number . ' procesado con éxito.');

            } elseif ($action === 'reject') {
                if (!$reason || trim($reason) === '') {
                    return redirect()->back()->with('error', 'Debe proporcionar un motivo para el rechazo.');
                }
                $service->reject($request, $reason);
                DB::commit();
                return redirect()->route('admin.requests.index')->with('warning', 'Solicitud RECHAZADA.');
            } 
            
            return redirect()->back()->with('error', 'Accion no valida.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function approve(RequestModel $request)
    {
        $this->authorize('solicitudes_aprobar');

        if ($request->status !== RequestModel::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya fue procesada o no existe.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $service = new \App\Services\InventoryRequestService();
            $service->approve($request);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud APROBADA y stock de productos rebajado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la aprobación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(HttpRequest $httpRequest, RequestModel $request)
    {
        $this->authorize('solicitudes_aprobar');

        if ($request->status !== RequestModel::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya fue procesada o no existe.'
            ], 422);
        }

        $reason = $httpRequest->input('rejection_reason');
        if (!$reason || trim($reason) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Debe proporcionar un motivo para el rechazo.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $service = new \App\Services\InventoryRequestService();
            $service->reject($request, $reason);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud RECHAZADA correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el rechazo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pdf(RequestModel $request)
    {
        try {
            $request->load(['requester', 'items.product.unit']);
            
            return \PDF::loadView('admin.requests.pdf', compact('request'))->stream('solicitud-' . $request->id . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de solicitud: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF. Por favor, contacte al administrador.');
        }
    }

    public function dispatchPdf(\App\Models\Dispatch $dispatch)
    {
        try {
            $dispatch->load(['request.requester', 'dispatcher', 'items.product.unit', 'items.batch']);
            
            return \PDF::loadView('admin.dispatches.pdf', compact('dispatch'))->stream('despacho-' . $dispatch->dispatch_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de despacho: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF de despacho. Por favor, contacte al administrador.');
        }
    }

    public function destroy($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(HttpRequest $request, $id) { return abort(404); }
}