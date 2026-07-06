<?php

namespace App\Http\Controllers;

use App\Events\StockUpdated;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\ProductBatch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Http\Requests\StoreStockInRequest;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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
        
        // Usar server-side si es AJAX o si hay parámetros de DataTables
        $isDataTables = $request->filled('draw') || $request->ajax();
        
        if ($isDataTables) {
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
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'LIKE', '%' . $request->invoice_number . '%');
        }

        if ($request->get('view_all') === 'true') {
            $stockIns = $query->paginate($perPage)->appends($request->except('page'));
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
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'LIKE', '%' . $request->invoice_number . '%');
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        
        // Siempre permitir ordenamiento
        $orderColumn = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        // Mapear índice de DataTables al campo de ordenamiento
        // 0: entry_date, 1: reference, 2: quantity, 3: unit_cost, 4: total, 5: supplier, 6: document
        $columnMap = [
            0 => 'entry_date',      // entry_date
            1 => 'purchase_order_id', // reference
            2 => 'quantity',     // quantity
            3 => 'unit_cost',    // unit_cost
            4 => 'total',       // total
            5 => 'supplier',    // supplier_id - usar join
            6 => 'document_type', // document
        ];
        $orderCol = $columnMap[$orderColumn] ?? 'id';

        // Ordenar por relación supplier usando join
        try {
            if ($orderCol === 'supplier') {
                $query->join('suppliers', 'stock_ins.supplier_id', '=', 'suppliers.id')
                      ->orderBy('suppliers.name', $orderDir)
                      ->select('stock_ins.*');
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } catch (\Exception $e) {
            $query->orderBy('id', 'desc');
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

            // Evitar división por cero
            $avgUnitCost = $totalQty > 0 ? $totalCost / $totalQty : 0;

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
                'id' => $item->id,
                'date' => $item->entry_date ? $item->entry_date->format('d/m/Y') : '',
                'reference' => $reference,
                'quantity' => '<span class="badge badge-success">+' . $totalQty . '</span>',
                'unit_cost' => '$' . number_format($avgUnitCost, 2),
                'total' => '$' . number_format($totalCost, 2),
                'supplier' => $item->supplier->name ?? 'Ajuste / N/A',
                'document' => $doc,
                'actions' => '<a href="' . route('admin.stock-in.show', $item->id) . '" class="btn btn-sm btn-info" title="Ver detalles"><i class="fas fa-eye"></i></a>',
                'items_data' => $item->items->map(function ($subItem) {
                    return [
                        'product_name' => $subItem->product->name ?? 'N/A',
                        'product_code' => $subItem->product->code ?? 'N/A',
                        'quantity' => $subItem->quantity,
                        'unit_cost' => '$' . number_format($subItem->unit_cost, 2),
                        'batch_number' => $subItem->batch_number ?? 'N/A',
                        'expiration_date' => $subItem->expiration_date ? \Carbon\Carbon::parse($subItem->expiration_date)->format('d/m/Y') : 'N/A',
                        'warehouse_location' => $subItem->warehouse_location ?? 'N/A',
                        'status' => $subItem->status === 'received' ? 'Recibido' : 'Rechazado'
                    ];
                })->toArray(),
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
        
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'requires_serial', 'is_perishable', 'category_id']);
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $locations = Location::orderBy('name')->pluck('name', 'id');
        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);


        $order = null;
        $orderItem = null;
        $selectedItemIds = [];

        if ($request->filled('order')) {
            $order = PurchaseOrder::with(['supplier', 'items.product'])
                ->find($request->order);

            if ($order) {
                if ($request->filled('item')) {
                    $orderItem = $order->items()
                        ->with('product')
                        ->where('id', $request->item)
                        ->where(function ($q) {
                            $q->whereColumn('quantity_received', '<', 'quantity');
                        })
                        ->first();
                }

                if ($request->filled('items')) {
                    $selectedItemIds = $request->input('items', []);
                }
            }
        }

        return view('admin.stock-in.create', compact('products', 'suppliers', 'locations', 'order', 'orderItem', 'selectedItemIds', 'categories'));

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
                'user_id' => Auth::id(),
            ];

            $stockIn = StockIn::create($stockInData);

            $totalQuantity = 0;
            $totalCost = 0;
            $firstProductId = null;
            $productsUpdated = [];

            foreach ($validatedData['items'] as $itemData) {
                $quantity = (int) $itemData['quantity'];
                $status = $itemData['status'] ?? 'received';
                
                if ($status === 'received') {
                    $totalQuantity += $quantity;
                    $totalCost += $quantity * $itemData['unit_cost'];
                }
                
                if ($firstProductId === null) {
                    $firstProductId = $itemData['product_id'];
                }

                $poItemId = $itemData['purchase_order_item_id'] ?? null;
                if (empty($poItemId) && !empty($validatedData['purchase_order_id'])) {
                    $matchedPoItem = PurchaseOrderItem::where('purchase_order_id', $validatedData['purchase_order_id'])
                        ->where('product_id', $itemData['product_id'])
                        ->first();
                    if ($matchedPoItem) {
                        $poItemId = $matchedPoItem->id;
                    }
                }

                $productModel = Product::find($itemData['product_id']);
                $requiresSerial = $productModel ? $productModel->requires_serial : false;
                $serialNumberInput = $requiresSerial ? ($itemData['serial_number'] ?? null) : null;

                $stockInItem = StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'purchase_order_item_id' => $poItemId,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $itemData['unit_cost'],
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiration_date' => $itemData['expiration_date'] ?? null,
                    'serial_number' => $serialNumberInput,
                    'warehouse_location' => $itemData['warehouse_location'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'status' => $status,
                    'rejection_reason' => $status === 'rejected' ? ($itemData['rejection_reason'] ?? null) : null,
                ]);

                if ($status === 'received') {
                    if (!empty($itemData['batch_number']) || !empty($itemData['expiration_date'])) {
                        if ($requiresSerial) {
                            $serials = array_filter(array_map('trim', explode(',', $serialNumberInput ?? '')));
                            foreach ($serials as $serial) {
                                ProductBatch::create([
                                    'product_id' => $itemData['product_id'],
                                    'stock_in_item_id' => $stockInItem->id,
                                    'batch_number' => $itemData['batch_number'] ?? null,
                                    'serial_number' => $serial,
                                    'expiration_date' => $itemData['expiration_date'] ?? null,
                                    'quantity' => 1,
                                    'unit_cost' => $itemData['unit_cost'],
                                    'invoice_number' => $stockIn->document_number,
                                ]);
                            }
                        } else {
                            $batch = ProductBatch::where('product_id', $itemData['product_id'])
                                ->where('batch_number', $itemData['batch_number'] ?? null)
                                ->whereNull('serial_number')
                                ->first();

                            if ($batch) {
                                $batch->quantity += $quantity;
                                $batch->stock_in_item_id = $stockInItem->id;
                                $batch->expiration_date = $itemData['expiration_date'] ?? null;
                                $batch->unit_cost = $itemData['unit_cost'];
                                $batch->invoice_number = $stockIn->document_number;
                                $batch->save();
                            } else {
                                ProductBatch::create([
                                    'product_id' => $itemData['product_id'],
                                    'stock_in_item_id' => $stockInItem->id,
                                    'batch_number' => $itemData['batch_number'] ?? null,
                                    'serial_number' => null,
                                    'expiration_date' => $itemData['expiration_date'] ?? null,
                                    'quantity' => $quantity,
                                    'unit_cost' => $itemData['unit_cost'],
                                    'invoice_number' => $stockIn->document_number,
                                ]);
                            }
                        }
                    }

                    $product = Product::lockForUpdate()->find($itemData['product_id']);
                    $product->stock += $quantity;
                    $product->cost = $itemData['unit_cost'];
                    $product->save();

                    $productsUpdated[$itemData['product_id']] = $product;
                }

                if (!empty($poItemId)) {
                    $orderItem = PurchaseOrderItem::find($poItemId);

                    if ($orderItem) {
                        if ($status === 'received') {
                            $orderItem->quantity_received += $quantity;
                        } else {
                            $orderItem->quantity_rejected += $quantity;
                        }
                        $orderItem->save();
                    }
                }
            }

            if (!empty($validatedData['purchase_order_id'])) {
                $affectedKitItems = PurchaseOrderItem::where('purchase_order_id', $validatedData['purchase_order_id'])
                    ->where('item_type', 'kit')
                    ->get();
                foreach ($affectedKitItems as $kitItem) {
                    $kitItem->recalculateKitQuantities();
                }

                $purchaseOrder = PurchaseOrder::with('items')->find($validatedData['purchase_order_id']);
                if ($purchaseOrder && $purchaseOrder->isFullyReceived() && $purchaseOrder->status === 'issued') {
                    $purchaseOrder->update(['status' => 'completed']);
                    session()->flash('oc_completed', "La Orden de Compra {$purchaseOrder->code} se ha completado automáticamente al recibir todos los productos.");
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
            $stockIn->load('items.product');
            $items = $stockIn->items;

            // 1. Lectura y Bloqueo (Lock) + 2. Validación de existencias
            $productsToLock = [];
            foreach ($items as $item) {
                if ($item->status === 'received') {
                    // Evitar bloquear el mismo producto múltiples veces si viene en más de una fila
                    if (!isset($productsToLock[$item->product_id])) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if (!$product) {
                            DB::rollback();
                            return redirect()->back()->with('error', 'Producto no encontrado.');
                        }
                        $productsToLock[$item->product_id] = $product;
                    } else {
                        $product = $productsToLock[$item->product_id];
                    }

                    // Verificar stock general
                    $newStock = $product->stock - $item->quantity;
                    if ($newStock < 0) {
                        DB::rollback();
                        return redirect()->back()
                            ->with('error', 'No es posible anular esta entrada. Parte del stock ya ha sido consumido o despachado del almacén.');
                    }

                    // Verificar lotes y seriales
                    $requiresSerial = $product->requires_serial;
                    if ($requiresSerial || !empty($item->serial_number)) {
                        $serials = array_filter(array_map('trim', explode(',', $item->serial_number ?? '')));
                        foreach ($serials as $serial) {
                            $batch = ProductBatch::where('product_id', $item->product_id)
                                ->where('serial_number', $serial)
                                ->first();
                            if (!$batch || $batch->quantity < 1) {
                                DB::rollback();
                                return redirect()->back()
                                    ->with('error', 'No es posible anular esta entrada. Parte del stock ya ha sido consumido o despachado del almacén.');
                            }
                        }
                    } elseif (!empty($item->batch_number)) {
                        $batch = ProductBatch::where('product_id', $item->product_id)
                            ->where('batch_number', $item->batch_number)
                            ->whereNull('serial_number')
                            ->first();
                        if (!$batch || $batch->quantity < $item->quantity) {
                            DB::rollback();
                            return redirect()->back()
                                ->with('error', 'No es posible anular esta entrada. Parte del stock ya ha sido consumido o despachado del almacén.');
                        }
                    }
                }
            }

            // 3. Reversa de Inventario y PO (Modificaciones de Datos)
            $affectedProducts = [];
            foreach ($items as $item) {
                if ($item->status === 'received') {
                    $product = $productsToLock[$item->product_id];
                    $product->stock -= $item->quantity;
                    $product->save();

                    // Reversar de lotes/seriales
                    $requiresSerial = $product->requires_serial;
                    if ($requiresSerial || !empty($item->serial_number)) {
                        $serials = array_filter(array_map('trim', explode(',', $item->serial_number ?? '')));
                        foreach ($serials as $serial) {
                            $batch = ProductBatch::where('product_id', $item->product_id)
                                ->where('serial_number', $serial)
                                ->first();
                            if ($batch) {
                                $batch->delete();
                            }
                        }
                    } elseif (!empty($item->batch_number)) {
                        $batch = ProductBatch::where('product_id', $item->product_id)
                            ->where('batch_number', $item->batch_number)
                            ->whereNull('serial_number')
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

                    // Reversar cantidades recibidas en PO
                    if ($item->purchase_order_item_id) {
                        $orderItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                        if ($orderItem) {
                            $orderItem->quantity_received -= $item->quantity;
                            if ($orderItem->quantity_received < 0) {
                                $orderItem->quantity_received = 0;
                            }
                            $orderItem->save();
                        }
                    }

                    $affectedProducts[$product->id] = $product;
                } elseif ($item->status === 'rejected') {
                    // Reversar cantidades rechazadas en PO
                    if ($item->purchase_order_item_id) {
                        $orderItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                        if ($orderItem) {
                            $orderItem->quantity_rejected -= $item->quantity;
                            if ($orderItem->quantity_rejected < 0) {
                                $orderItem->quantity_rejected = 0;
                            }
                            $orderItem->save();
                        }
                    }
                }
            }

            // Recalcular cantidades de kits si los hay
            $affectedKitItemIds = [];
            foreach ($items as $item) {
                if ($item->purchase_order_item_id) {
                    $orderItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                    if ($orderItem && $orderItem->item_type === 'kit') {
                        $affectedKitItemIds[] = $orderItem->id;
                    }
                }
            }

            foreach (array_unique($affectedKitItemIds) as $poItemId) {
                $orderItem = PurchaseOrderItem::find($poItemId);
                if ($orderItem) {
                    $orderItem->recalculateKitQuantities();
                }
            }

            // Reabrir PO si era completed y ahora tiene pendientes
            if ($stockIn->purchase_order_id) {
                $purchaseOrder = PurchaseOrder::with('items')->find($stockIn->purchase_order_id);
                if ($purchaseOrder) {
                    $purchaseOrder->load('items');
                    if ($purchaseOrder->status === 'completed' && !$purchaseOrder->isFullyReceived()) {
                        $purchaseOrder->update(['status' => 'issued']);
                    }
                }
            }

            // Disparar eventos y limpiar caché para productos modificados
            foreach ($affectedProducts as $product) {
                event(new StockUpdated(
                    product: $product,
                    quantity: $product->stock,
                    type: 'out',
                    referenceId: $stockIn->id,
                    referenceType: StockIn::class,
                    notes: 'Anulación de entrada de stock'
                ));

                $this->cacheService->invalidateProductStock($product->id);
            }

            // 4. [CRÍTICO] Eliminación Física del Registro al Final
            $stockIn->delete();

            DB::commit();

            return redirect()->route('admin.stock-in.index')
                ->with('success', 'Entrada de stock anulada correctamente y existencias revertidas.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    public function revertItems(Request $request, StockIn $stockIn)
    {
        $this->authorize('entradas_eliminar');

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:stock_in_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $stockIn->load('items.product');
            $affectedProducts = [];
            $allFullyReverted = true;

            foreach ($validated['items'] as $revertData) {
                $stockInItem = $stockIn->items->firstWhere('id', $revertData['id']);
                if (!$stockInItem) {
                    DB::rollback();
                    return response()->json(['message' => 'Item no pertenece a esta entrada de stock.'], 422);
                }

                if ($stockInItem->status !== 'received') {
                    continue;
                }

                $quantity = (int) $revertData['quantity'];

                if ($quantity > $stockInItem->quantity) {
                    DB::rollback();
                    return response()->json([
                        'message' => "La cantidad a revertir ({$quantity}) excede la cantidad recibida ({$stockInItem->quantity}) para el producto."
                    ], 422);
                }

                $product = Product::lockForUpdate()->find($stockInItem->product_id);
                if (!$product) {
                    DB::rollback();
                    return response()->json(['message' => 'Producto no encontrado.'], 422);
                }

                $newStock = $product->stock - $quantity;
                if ($newStock < 0) {
                    DB::rollback();
                    return response()->json([
                        'message' => "No es posible revertir {$product->name}. Stock actual ({$product->stock}) insuficiente para revertir {$quantity}."
                    ], 422);
                }

                // Reversar stock del producto
                $product->stock = $newStock;
                $product->save();

                // Reversar lotes/seriales
                $requiresSerial = $product->requires_serial;
                if ($requiresSerial || !empty($stockInItem->serial_number)) {
                    $serials = array_filter(array_map('trim', explode(',', $stockInItem->serial_number ?? '')));
                    foreach ($serials as $serial) {
                        $batch = ProductBatch::where('product_id', $stockInItem->product_id)
                            ->where('serial_number', $serial)
                            ->first();
                        if ($batch) {
                            $batch->delete();
                        }
                    }
                } elseif (!empty($stockInItem->batch_number)) {
                    $batch = ProductBatch::where('product_id', $stockInItem->product_id)
                        ->where('batch_number', $stockInItem->batch_number)
                        ->whereNull('serial_number')
                        ->first();
                    if ($batch) {
                        $batch->quantity -= $quantity;
                        if ($batch->quantity <= 0) {
                            $batch->delete();
                        } else {
                            $batch->save();
                        }
                    }
                }

                // Reversar cantidades recibidas en PO
                if ($stockInItem->purchase_order_item_id) {
                    $orderItem = PurchaseOrderItem::find($stockInItem->purchase_order_item_id);
                    if ($orderItem) {
                        $orderItem->quantity_received -= $quantity;
                        if ($orderItem->quantity_received < 0) {
                            $orderItem->quantity_received = 0;
                        }
                        $orderItem->save();
                    }
                }

                // Reducir cantidad en el StockInItem
                $stockInItem->quantity -= $quantity;
                $stockInItem->save();

                if ($stockInItem->quantity > 0) {
                    $allFullyReverted = false;
                }

                $affectedProducts[$product->id] = $product;
            }

            // Recalcular cantidades de kits si aplica
            foreach ($validated['items'] as $revertData) {
                $stockInItem = $stockIn->items->firstWhere('id', $revertData['id']);
                if ($stockInItem && $stockInItem->purchase_order_item_id) {
                    $orderItem = PurchaseOrderItem::find($stockInItem->purchase_order_item_id);
                    if ($orderItem && $orderItem->item_type === 'kit') {
                        $orderItem->recalculateKitQuantities();
                    }
                }
            }

            // Reabrir PO si estaba completed y ya no está totalmente recibida
            if ($stockIn->purchase_order_id) {
                $purchaseOrder = PurchaseOrder::with('items')->find($stockIn->purchase_order_id);
                if ($purchaseOrder && $purchaseOrder->status === 'completed' && !$purchaseOrder->isFullyReceived()) {
                    $purchaseOrder->update(['status' => 'issued']);
                }
            }

            // Disparar eventos
            foreach ($affectedProducts as $product) {
                event(new StockUpdated(
                    product: $product,
                    quantity: $product->stock,
                    type: 'out',
                    referenceId: $stockIn->id,
                    referenceType: StockIn::class,
                    notes: 'Revertido selectivo de entrada de stock'
                ));
                $this->cacheService->invalidateProductStock($product->id);
            }

            // Si todos los items están en 0, eliminar el registro completo
            $stockInDeleted = false;
            if ($allFullyReverted) {
                $remainingTotal = $stockIn->items()->sum('quantity');
                if ($remainingTotal <= 0) {
                    $stockIn->delete();
                    $stockInDeleted = true;
                }
            }

            DB::commit();

            return response()->json([
                'message' => $stockInDeleted
                    ? 'Todos los items fueron revertidos. La entrada de stock ha sido eliminada.'
                    : 'Stock revertido correctamente.',
                'stockInDeleted' => $stockInDeleted
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error al revertir: ' . $e->getMessage()], 500);
        }
    }

    public function show(StockIn $stockIn)
    {
        $this->authorize('entradas_ver');
        
        $stockIn->load(['items.product', 'supplier', 'user', 'purchaseOrder', 'replacements.items']);
        
        return view('admin.stock-in.show', compact('stockIn'));
    }

    public function downloadPDF(StockIn $stockIn)
    {
        try {
            $this->authorize('entradas_ver');
            $stockIn->load(['items.product', 'supplier', 'user', 'purchaseOrder']);
            $pdf = Pdf::loadView('admin.stock-in.pdf', compact('stockIn'));
            return $pdf->stream('ENT-' . str_pad($stockIn->id, 4, '0', STR_PAD_LEFT) . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de entrada de stock: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF. Por favor, contacte al administrador.');
        }
    }

    public function createReplacement(StockIn $stockIn)
    {
        $this->authorize('entradas_crear');

        $originalStockIn = $stockIn->load(['items.product', 'supplier', 'purchaseOrder']);

        $rejectedItems = $originalStockIn->items()->where('status', 'rejected')->get();

        if ($rejectedItems->isEmpty()) {
            return redirect()->route('admin.stock-in.show', $stockIn->id)
                ->with('error', 'No hay productos rechazados para reemplazar en esta entrada.');
        }

        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'requires_serial']);
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $locations = Location::orderBy('name')->pluck('name', 'id');

        return view('admin.stock-in.create-replacement', compact('products', 'suppliers', 'locations', 'originalStockIn', 'rejectedItems'));
    }

    public function storeReplacement(Request $request)
    {
        $this->authorize('entradas_crear');

        $validatedData = $request->validate([
            'original_stock_in_id' => ['required', 'exists:stock_ins,id'],
            'supplier_id' => ['nullable'],
            'purchase_order_id' => ['nullable'],
            'document_type' => ['nullable', 'string', 'max:50'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'delivery_note_number' => ['nullable', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
            'items.*.batch_number' => ['nullable', 'string', 'max:50'],
            'items.*.expiration_date' => ['nullable', 'date'],
            'items.*.serial_number' => ['nullable', 'string', 'max:100'],
            'items.*.warehouse_location' => ['nullable', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.replaced_item_id' => ['required', 'exists:stock_in_items,id'],
        ]);

        // Validar fecha de vencimiento obligatoria para productos perecederos
        foreach ($request->input('items', []) as $index => $item) {
            $productId = $item['product_id'] ?? null;
            $expirationDate = $item['expiration_date'] ?? null;
            if ($productId) {
                $product = Product::find($productId);
                if ($product && $product->is_perishable && empty($expirationDate)) {
                    return back()->withErrors(["items.$index.expiration_date" => "La fecha de vencimiento es obligatoria para productos perecederos."])->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $originalStockIn = StockIn::findOrFail($validatedData['original_stock_in_id']);

            $stockInData = [
                'supplier_id' => $validatedData['supplier_id'] ?? $originalStockIn->supplier_id,
                'purchase_order_id' => $validatedData['purchase_order_id'] ?? $originalStockIn->purchase_order_id,
                'document_type' => $validatedData['document_type'] ?? null,
                'document_number' => $validatedData['document_number'] ?? null,
                'invoice_number' => $validatedData['invoice_number'] ?? null,
                'delivery_note_number' => $validatedData['delivery_note_number'] ?? null,
                'reason' => $validatedData['reason'] ?? 'Reemplazo de productos rechazados',
                'entry_date' => $validatedData['entry_date'],
                'user_id' => Auth::id(),
                'type' => 'replacement',
                'original_stock_in_id' => $originalStockIn->id,
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

                $rejectedItem = StockInItem::find($itemData['replaced_item_id']);
                $poItemId = $rejectedItem ? $rejectedItem->purchase_order_item_id : null;

                $productModel = Product::find($itemData['product_id']);
                $requiresSerial = $productModel ? $productModel->requires_serial : false;
                $serialNumberInput = $requiresSerial ? ($itemData['serial_number'] ?? null) : null;

                $stockInItem = StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'purchase_order_item_id' => $poItemId,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $itemData['unit_cost'],
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiration_date' => $itemData['expiration_date'] ?? null,
                    'serial_number' => $serialNumberInput,
                    'warehouse_location' => $itemData['warehouse_location'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'status' => 'received',
                    'replaced_item_id' => $itemData['replaced_item_id'],
                ]);

                if (!empty($itemData['batch_number']) || !empty($itemData['expiration_date'])) {
                    if ($requiresSerial) {
                        $serials = array_filter(array_map('trim', explode(',', $serialNumberInput ?? '')));
                        foreach ($serials as $serial) {
                            ProductBatch::create([
                                'product_id' => $itemData['product_id'],
                                'stock_in_item_id' => $stockInItem->id,
                                'batch_number' => $itemData['batch_number'] ?? null,
                                'serial_number' => $serial,
                                'expiration_date' => $itemData['expiration_date'] ?? null,
                                'quantity' => 1,
                                'unit_cost' => $itemData['unit_cost'],
                                'invoice_number' => $stockIn->document_number,
                            ]);
                        }
                    } else {
                        $batch = ProductBatch::where('product_id', $itemData['product_id'])
                            ->where('batch_number', $itemData['batch_number'] ?? null)
                            ->whereNull('serial_number')
                            ->first();

                        if ($batch) {
                            $batch->quantity += $quantity;
                            $batch->stock_in_item_id = $stockInItem->id;
                            $batch->expiration_date = $itemData['expiration_date'] ?? null;
                            $batch->unit_cost = $itemData['unit_cost'];
                            $batch->invoice_number = $stockIn->document_number;
                            $batch->save();
                        } else {
                            ProductBatch::create([
                                'product_id' => $itemData['product_id'],
                                'stock_in_item_id' => $stockInItem->id,
                                'batch_number' => $itemData['batch_number'] ?? null,
                                'serial_number' => null,
                                'expiration_date' => $itemData['expiration_date'] ?? null,
                                'quantity' => $quantity,
                                'unit_cost' => $itemData['unit_cost'],
                                'invoice_number' => $stockIn->document_number,
                            ]);
                        }
                    }
                }

                $product = Product::lockForUpdate()->find($itemData['product_id']);
                $product->stock += $quantity;
                $product->cost = $itemData['unit_cost'];
                $product->save();

                $productsUpdated[$itemData['product_id']] = $product;

                if ($rejectedItem) {
                    $rejectedItem->status = 'replaced';
                    $rejectedItem->save();
                }

                if ($poItemId) {
                    $orderItem = PurchaseOrderItem::find($poItemId);

                    if ($orderItem) {
                        $orderItem->quantity_received += $quantity;
                        $orderItem->quantity_replaced += $quantity;
                        $orderItem->quantity_rejected = max(0, $orderItem->quantity_rejected - $quantity);
                        $orderItem->save();
                    }
                }
            }

            if ($stockIn->purchase_order_id) {
                $affectedKitItems = PurchaseOrderItem::where('purchase_order_id', $stockIn->purchase_order_id)
                    ->where('item_type', 'kit')
                    ->get();
                foreach ($affectedKitItems as $kitItem) {
                    $kitItem->recalculateKitQuantities();
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
                    notes: 'Reemplazo de productos rechazados'
                ));

                $this->cacheService->invalidateProductStock($product->id);
            }

            return redirect()->route('admin.stock-in.show', $stockIn->id)
                ->with('success', 'Entrada de reemplazo registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar la entrada de reemplazo: ' . $e->getMessage());
        }
    }
    
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
}
