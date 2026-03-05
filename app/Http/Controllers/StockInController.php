<?php

namespace App\Http\Controllers;

use App\Events\StockUpdated;
use App\Models\StockIn;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Http\Requests\StoreStockInRequest;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(StockIn::class, 'stockIn');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->indexDataTables($request);
        }

        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $products = Product::orderBy('name')->pluck('name', 'id');

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $query = StockIn::with(['product', 'supplier', 'user', 'purchaseOrder'])->orderBy('entry_date', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->get('view_all') === 'true') {
            $stockIns = $query->paginate($query->count())->appends($request->except('page'));
        } else {
            $stockIns = $query->paginate($perPage)->appends($request->except('per_page'));
        }

        return view('admin.stock-in.index', compact('stockIns', 'suppliers', 'products', 'perPage'));
    }

    protected function indexDataTables(Request $request)
    {
        $query = StockIn::with(['product', 'supplier', 'user', 'purchaseOrder']);

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(observations) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")]);
                  })
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")]);
                  });
            });
        }

        $totalRecords = StockIn::count();
        $totalFiltered = $query->count();

        $columns = ['entry_date', 'quantity', 'product_id', 'supplier_id', 'unit_cost', 'created_at'];
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $stockIns = $query->offset($start)->limit($length)->get();

        $data = $stockIns->map(function ($item) {
            $doc = $item->document_type ?? '';
            if ($item->document_number) {
                $doc .= ' <span class="badge badge-light">' . $item->document_number . '</span>';
            }
            return [
                'date' => $item->entry_date->format('d/m/Y'),
                'product' => ($item->product->name ?? 'N/A') . '<br><small class="text-muted">' . ($item->product->code ?? '') . '</small>',
                'quantity' => '<span class="badge badge-success">+' . $item->quantity . ' ' . ($item->product->unit->abbreviation ?? '') . '</span>',
                'unit_cost' => '$' . number_format($item->unit_cost, 2),
                'total' => '$' . number_format($item->quantity * $item->unit_cost, 2),
                'supplier' => $item->supplier->name ?? 'Ajuste / N/A',
                'document' => $doc,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        $order = null;
        $orderItem = null;

        if ($request->filled('order')) {
            $order = PurchaseOrder::with(['supplier', 'items.product'])
                ->where('status', 'issued')
                ->find($request->order);

            if ($order && $request->filled('item')) {
                $orderItem = $order->items->where('id', $request->item)->where('quantity_received', '<', \DB::raw('quantity'))->first();
            }
        }

        return view('admin.stock-in.create', compact('products', 'suppliers', 'order', 'orderItem'));
    }

    public function store(StoreStockInRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $stockIn = StockIn::create($validatedData + ['user_id' => auth()->id()]);

            $product = Product::lockForUpdate()->find($validatedData['product_id']);

            $newQuantity = $product->stock + $validatedData['quantity'];
            $newCost = $validatedData['unit_cost'];

            $product->stock = $newQuantity;
            $product->cost = $newCost;
            $product->save();

            if (!empty($validatedData['purchase_order_id'])) {
                $order = PurchaseOrder::find($validatedData['purchase_order_id']);
                $orderItem = $order->items()
                    ->where('product_id', $validatedData['product_id'])
                    ->first();

                if ($orderItem) {
                    $orderItem->quantity_received += $validatedData['quantity'];
                    $orderItem->save();
                }
            }

            DB::commit();

            event(new StockUpdated(
                product: $product,
                quantity: $validatedData['quantity'],
                type: 'in',
                referenceId: $stockIn->id,
                referenceType: StockIn::class,
                notes: $validatedData['reason'] ?? null
            ));

            $this->cacheService->invalidateProductStock($validatedData['product_id']);

            return redirect()->route('admin.stock-in.index')
                ->with('success', 'Entrada de stock registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar la entrada de stock: ' . $e->getMessage());
        }
    }

    public function destroy(StockIn $stockIn)
    {
        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->find($stockIn->product_id);

            if ($product->stock < $stockIn->quantity) {
                DB::rollback();
                return redirect()->route('admin.stock-in.index')
                    ->with('error', 'No se puede eliminar la entrada: El stock actual es menor a la cantidad ingresada.');
            }

            $product->stock -= $stockIn->quantity;
            $product->save();

            if ($stockIn->purchase_order_id) {
                $orderItem = PurchaseOrderItem::where('purchase_order_id', $stockIn->purchase_order_id)
                    ->where('product_id', $stockIn->product_id)
                    ->first();

                if ($orderItem) {
                    $orderItem->quantity_received -= $stockIn->quantity;
                    if ($orderItem->quantity_received < 0) {
                        $orderItem->quantity_received = 0;
                    }
                    $orderItem->save();
                }
            }

            $stockIn->delete();

            DB::commit();

            event(new StockUpdated(
                product: $product,
                quantity: $stockIn->quantity,
                type: 'in',
                referenceId: $stockIn->id,
                referenceType: StockIn::class,
                notes: 'Eliminación de entrada de stock'
            ));

            $this->cacheService->invalidateProductStock($stockIn->product_id);

            return redirect()->route('admin.stock-in.index')
                ->with('success', 'Entrada de stock eliminada y stock del producto corregido.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    public function show($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
}
