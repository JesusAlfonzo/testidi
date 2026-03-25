<?php

namespace App\Http\Controllers;

use App\Events\StockUpdated;
use App\Models\InventoryRequest as RequestModel;
use App\Models\Product;
use App\Models\Kit;
use App\Models\RequestItem;
use App\Models\User;
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
        $this->authorizeResource(RequestModel::class, 'inventoryRequest');
        $this->middleware('can:solicitudes_aprobar')->only('process');
    }

    public function index(HttpRequest $request)
    {
        if ($request->ajax()) {
            return $this->indexDataTables($request);
        }

        // 1. Cargar lista de usuarios para el filtro
        $requesters = User::pluck('name', 'id');

        // Determinar cantidad por página
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        // 2. Iniciar consulta base
        $query = RequestModel::with(['requester', 'approver', 'items.product'])
            ->orderBy('requested_at', 'desc');

        // 3. Aplicar Filtros de Seguridad (Admin ve todas, usuarios normales solo las propias)
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->can('solicitudes_aprobar')) {
            $query->where('requester_id', $user->id);
        }

        // 4. Aplicar Filtros de Búsqueda
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

        // 5. Obtener resultados con paginación
        if ($request->get('view_all') === 'true') {
            $requests = $query->paginate($perPage)->appends($request->except('page'));
        } else {
            $requests = $query->paginate($perPage)->appends($request->except('per_page'));
        }

        return view('admin.requests.index', compact('requests', 'requesters', 'perPage'));
    }

    protected function indexDataTables(HttpRequest $request)
    {
        $user = auth()->user();
        
        $query = RequestModel::with(['requester', 'approver']);

        // Admin ve todas las solicitudes, usuarios normales solo las propias
        if (!$user->isSuperAdmin() && !$user->can('solicitudes_aprobar')) {
            $query->where('requester_id', $user->id);
        }

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

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        
        $isInitialLoad = !$search && !$request->filled('date_from') && !$request->filled('date_to') 
            && !$request->filled('status') && !$request->filled('requester_id');
        
        if ($isInitialLoad) {
            $query->orderBy('created_at', 'desc');
        } else {
            $orderColumn = $request->input('order.0.column', 5);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['id', 'requested_at', 'status', 'requester_id', 'processed_at', 'created_at'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(justification) LIKE ?', [strtolower("%{$search}%")])
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
            
            if ($item->status === 'Pending') {
                $statusLabel = 'Pendiente';
                $statusClass = 'warning';
            } elseif ($item->status === 'Approved') {
                $statusLabel = 'Aprobada';
                $statusClass = 'success';
            } elseif ($item->status === 'Rejected') {
                $statusLabel = 'Rechazada';
                $statusClass = 'danger';
            } else {
                $statusLabel = $item->status;
            }
            
            return [
                'id' => 'REQ-' . $item->id,
                'date' => $item->requested_at->format('d/m/Y H:i'),
                'requester' => $item->requester->name ?? 'N/A',
                'justification' => Str::limit($item->justification, 50),
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
        // Cargar productos y kits activos para el selector
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'stock']);
        $kits = Kit::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        
        return view('admin.requests.create', compact('products', 'kits'));
    }

    // Lógica: Guardar la solicitud y sus items
    public function store(StoreRequestRequest $request)
    {
        $this->authorize('solicitudes_crear');
        
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // 1. Crear la cabecera de la solicitud
            $requestModel = RequestModel::create([
                'requester_id' => auth()->id(),
                'status' => 'Pending',
                'justification' => $validatedData['justification'],
                'destination_area' => $validatedData['destination_area'] ?? null,
                'requested_at' => Carbon::now(),
            ]);

            // 2. OPTIMIZACIÓN N+1: Preparar datos en memoria para precios
            $itemsCollection = collect($validatedData['items']);
            
            // Extraer IDs
            $productIds = $itemsCollection->where('item_type', 'product')->pluck('product_id')->filter()->toArray();
            $kitIds = $itemsCollection->where('item_type', 'kit')->pluck('kit_id')->filter()->toArray();

            // Cargar diccionarios de precios
            $productsDict = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $kitsDict = Kit::whereIn('id', $kitIds)->get()->keyBy('id');

            $itemsToStore = [];

            foreach ($validatedData['items'] as $item) {
                $price = 0;
                $productId = null;
                $kitId = null;

                // Lógica para PRODUCTO
                if ($item['item_type'] === 'product') {
                    $productId = $item['product_id'];
                    $prod = $productsDict->get($productId);
                    $price = $prod ? $prod->cost : 0; 
                } 
                // Lógica para KIT
                elseif ($item['item_type'] === 'kit') {
                    $kitId = $item['kit_id'];
                    $kit = $kitsDict->get($kitId);
                    $price = $kit ? $kit->unit_price : 0;
                }

                // Preparamos el array para guardar
                $itemsToStore[] = [
                    'product_id' => $productId,
                    'kit_id' => $kitId,
                    'item_type' => $item['item_type'],
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
    public function show(RequestModel $requestModel)
    {
        $this->authorize('solicitudes_ver');
        
        $requestModel->load([
            'requester',
            'approver',
            'items.product.unit',
            'items.kit.components.unit', // Carga componentes de kits
        ]);

        return view('admin.requests.show', compact('requestModel'));
    }

    // Método especializado para APROBAR o RECHAZAR una solicitud
    public function process(HttpRequest $httpRequest, RequestModel $request)
    {
        if (!$request || $request->status !== 'Pending') {
            return redirect()->back()->with('error', 'Esta solicitud ya fue procesada o no existe.');
        }

        $action = $httpRequest->input('action');
        $reason = $httpRequest->input('rejection_reason');

        $service = new InventoryRequestService();

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $service->approve($request);
                DB::commit();
                return redirect()->route('admin.requests.index')->with('success', 'Solicitud APROBADA y stock actualizado correctamente.');

            } elseif ($action === 'reject') {
                $service->reject($request, $reason);
                DB::commit();
                return redirect()->route('admin.requests.index')->with('warning', 'Solicitud RECHAZADA.');
            } 
            
            return redirect()->back()->with('error', 'Accion no valida.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al procesar la solicitud.');
        }
    }

    public function pdf(RequestModel $request)
    {
        $request->load(['requester', 'items.product.unit', 'items.kit']);
        
        return \PDF::loadView('admin.requests.pdf', compact('request'))->stream('solicitud-' . $request->id . '.pdf');
    }

    public function destroy($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(HttpRequest $request, $id) { return abort(404); }
}