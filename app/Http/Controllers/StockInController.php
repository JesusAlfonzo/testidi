<?php

namespace App\Http\Controllers;

use App\Events\StockUpdated;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\ProductBatch;
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
    }

    public function index(Request $request)
    {
        $this->authorize('entradas_ver');
        
        if ($request->ajax()) {
            return $this->indexDataTables($request);
        }

        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $products = Product::orderBy('name')->pluck('name', 'id');

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $query = StockIn::with(['items.product', 'supplier', 'user', 'purchaseOrder'])->orderBy('entry_date', 'desc');

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
        $query = StockIn::with(['items.product', 'supplier', 'user', 'purchaseOrder']);

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
            $query->whereHas('items', function($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        
        $isInitialLoad = !$search && !$request->filled('date_from') && !$request->filled('date_to') 
            && !$request->filled('supplier_id') && !$request->filled('product_id');
        
        if ($isInitialLoad) {
            $query->orderBy('created_at', 'desc');
        } else {
            $orderColumn = $request->input('order.0.column', 5);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['entry_date', 'quantity', 'product_id', 'supplier_id', 'unit_cost', 'created_at'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(reason) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereHas('items.product', function($pq) use ($search) {
                      $pq->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")]);
                  })
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")]);
                  });
            });
        }

        $totalRecords = StockIn::count();
        $totalFiltered = $query->count();

        $stockIns = $query->offset($start)->limit($length)->get();

        $data = $stockIns->map(function ($item) {
            $doc = $item->document_type ?? '';
            if ($item->document_number) {
                $doc .= ' <span class="badge badge-light">' . $item->document_number . '</span>';
            }
            if ($item->invoice_number) {
                $doc .= ' <span class="badge badge-info">Fac: ' . $item->invoice_number . '</span>';
            }
            if ($item->delivery_note_number) {
                $doc .= ' <span class="badge badge-secondary">N/D: ' . $item->delivery_note_number . '</span>';
            }

            $totalQty = 0;
            $totalCost = 0;
            $itemCount = $item->items->count();
            foreach ($item->items as $stockItem) {
                $totalQty += $stockItem->quantity;
                $totalCost += $stockItem->quantity * $stockItem->unit_cost;
            }

            $reference = '';
            if ($item->purchaseOrder) {
                $reference = '<span class="badge badge-primary"><i class="fas fa-file-contract"></i> ' . $item->purchaseOrder->code . '</span>';
            } elseif ($item->supplier) {
                $reference = '<span class="badge badge-info"><i class="fas fa-truck"></i> ' . $item->supplier->name . '</span>';
            } else {
                $reference = '<span class="badge badge-secondary"><i class="fas fa-boxes"></i> ' . ($item->reason ?? 'Ajuste') . '</span>';
            }
            $reference .= ' <span class="badge badge-light">' . $itemCount . ' item' . ($itemCount > 1 ? 's' : '') . '</span>';

            return [
                'date' => $item->entry_date->format('d/m/Y'),
                'reference' => $reference,
                'quantity' => '<span class="badge badge-success">+' . $totalQty . '</span>',
                'unit_cost' => '$' . number_format($totalCost / $totalQty, 2),
                'total' => '$' . number_format($totalCost, 2),
                'supplier' => $item->supplier->name ?? 'Ajuste / N/A',
                'document' => $doc,
                'actions' => '<a href="' . route('admin.stock-in.show', $item->id) . '" class="btn btn-sm btn-info" title="Ver detalles"><i class="fas fa-eye"></i></a>',
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
        $this->authorize('entradas_crear');
        
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        $order = null;
        $orderItem = null;

        if ($request->filled('order')) {
            $order = PurchaseOrder::with(['supplier', 'items.product'])
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
            $stockInData = [
                'supplier_id' => $validatedData['supplier_id'] ?? null,
                'purchase_order_id' => $validatedData['purchase_order_id'] ?? null,
                'document_type' => $validatedData['document_type'] ?? null,
                'document_number' => $validatedData['document_number'] ?? null,
                'invoice_number' => $validatedData['invoice_number'] ?? null,
                'delivery_note_number' => $validatedData['delivery_note_number'] ?? null,
                'reason' => $validatedData['reason'] ?? null,
                'entry_date' => $validatedData['entry_date'],
                'user_id' => auth()->id(),
            ];

            $stockIn = StockIn::create($stockInData);

            $totalQuantity = 0;
            $totalCost = 0;
            $firstProductId = null;
            $productsUpdated = [];

            foreach ($validatedData['items'] as $itemData) {
                $quantity = (int) $itemData['quantity'];
                $totalQuantity += $quantity;
                $totalCost += $quantity * $itemData['unit_cost'];
                
                if ($firstProductId === null) {
                    $firstProductId = $itemData['product_id'];
                }

                $stockInItem = StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $itemData['unit_cost'],
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'serial_number' => $itemData['serial_number'] ?? null,
                    'warehouse_location' => $itemData['warehouse_location'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                if (!empty($itemData['batch_number']) || !empty($itemData['expiry_date'])) {
                    $batch = ProductBatch::where('product_id', $itemData['product_id'])
                        ->where('batch_number', $itemData['batch_number'] ?? null)
                        ->where('serial_number', $itemData['serial_number'] ?? null)
                        ->first();

                    if ($batch) {
                        $batch->quantity += $quantity;
                        $batch->stock_in_item_id = $stockInItem->id;
                        $batch->expiry_date = $itemData['expiry_date'] ?? null;
                        $batch->unit_cost = $itemData['unit_cost'];
                        $batch->save();
                    } else {
                        ProductBatch::create([
                            'product_id' => $itemData['product_id'],
                            'stock_in_item_id' => $stockInItem->id,
                            'batch_number' => $itemData['batch_number'] ?? null,
                            'serial_number' => $itemData['serial_number'] ?? null,
                            'expiry_date' => $itemData['expiry_date'] ?? null,
                            'quantity' => $quantity,
                            'unit_cost' => $itemData['unit_cost'],
                        ]);
                    }
                }

                $product = Product::lockForUpdate()->find($itemData['product_id']);
                $product->stock += $quantity;
                $product->cost = $itemData['unit_cost'];
                $product->save();

                $productsUpdated[$itemData['product_id']] = $product;

                if (!empty($validatedData['purchase_order_id'])) {
                    $orderItem = PurchaseOrderItem::where('purchase_order_id', $validatedData['purchase_order_id'])
                        ->where('product_id', $itemData['product_id'])
                        ->first();

                    if ($orderItem) {
                        $orderItem->quantity_received += $quantity;
                        $orderItem->save();
                    }
                }
            }

            $stockIn->product_id = $firstProductId;
            $stockIn->quantity = $totalQuantity;
            $stockIn->unit_cost = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
            $stockIn->save();

            DB::commit();

            foreach ($productsUpdated as $product) {
                event(new StockUpdated(
                    product: $product,
                    quantity: $product->stock,
                    type: 'in',
                    referenceId: $stockIn->id,
                    referenceType: StockIn::class,
                    notes: $validatedData['reason'] ?? null
                ));

                $this->cacheService->invalidateProductStock($product->id);
            }

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
        $this->authorize('entradas_eliminar');
        
        DB::beginTransaction();
        try {
            $items = $stockIn->items;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);

                if ($product->stock < $item->quantity) {
                    DB::rollback();
                    return redirect()->route('admin.stock-in.index')
                        ->with('error', "No se puede eliminar la entrada: El stock actual del producto {$product->name} es menor a la cantidad ingresada.");
                }

                $product->stock -= $item->quantity;
                $product->save();

                if (!empty($item->batch_number) || !empty($item->serial_number)) {
                    $batch = ProductBatch::where('product_id', $item->product_id)
                        ->where('batch_number', $item->batch_number)
                        ->where('serial_number', $item->serial_number)
                        ->first();

                    if ($batch) {
                        $batch->quantity -= $item->quantity;
                        if ($batch->quantity <= 0) {
                            $batch->delete();
                        } else {
                            $batch->save();
                        }
                    }
                }

                if ($stockIn->purchase_order_id) {
                    $orderItem = PurchaseOrderItem::where('purchase_order_id', $stockIn->purchase_order_id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($orderItem) {
                        $orderItem->quantity_received -= $item->quantity;
                        if ($orderItem->quantity_received < 0) {
                            $orderItem->quantity_received = 0;
                        }
                        $orderItem->save();
                    }
                }

                event(new StockUpdated(
                    product: $product,
                    quantity: $product->stock,
                    type: 'in',
                    referenceId: $stockIn->id,
                    referenceType: StockIn::class,
                    notes: 'Eliminación de entrada de stock'
                ));

                $this->cacheService->invalidateProductStock($item->product_id);
            }

            $stockIn->delete();

            DB::commit();

            return redirect()->route('admin.stock-in.index')
                ->with('success', 'Entrada de stock eliminada y stock del producto corregido.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    public function show(StockIn $stockIn)
    {
        $this->authorize('entradas_ver');
        
        $stockIn->load(['items.product', 'supplier', 'user', 'purchaseOrder']);
        
        return view('admin.stock-in.show', compact('stockIn'));
    }
    
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
}
