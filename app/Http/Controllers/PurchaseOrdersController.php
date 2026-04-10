<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseQuote;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\OrderCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrdersController extends Controller
{
    public function searchSuppliers(Request $request)
    {
        $search = $request->get('q', '');
        
        $suppliers = Supplier::where(function($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")])
                      ->orWhereRaw('LOWER(email) LIKE ?', [strtolower("%{$search}%")])
                      ->orWhereRaw('LOWER(tax_id) LIKE ?', [strtolower("%{$search}%")]);
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function($supplier) {
                return [
                    'id' => $supplier->id,
                    'text' => $supplier->name . ' | ' . ($supplier->email ?? 'Sin email'),
                    'name' => $supplier->name,
                    'email' => $supplier->email,
                ];
            });

        return response()->json(['results' => $suppliers]);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        
        $products = Product::where(function($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")])
                      ->orWhereRaw('LOWER(code) LIKE ?', [strtolower("%{$search}%")]);
            })
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'text' => $product->name . ' (' . ($product->code ?? 'S/C') . ')',
                    'name' => $product->name,
                    'code' => $product->code,
                    'unit' => $product->unit->abbreviation ?? 'und',
                ];
            });

        return response()->json(['results' => $products]);
    }
    public function __construct()
    {
        $this->middleware('can:ordenes_compra_ver')->only(['index', 'show']);
        $this->middleware('can:ordenes_compra_crear')->only(['create', 'store']);
        $this->middleware('can:ordenes_compra_editar')->only(['edit', 'update']);
        $this->middleware('can:ordenes_compra_eliminar')->only(['destroy']);
        $this->middleware('can:ordenes_compra_aprobar')->only(['issue', 'complete']);
        $this->middleware('can:ordenes_compra_anular')->only('cancel');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        // Usar server-side si es AJAX o si hay parámetros de DataTables
        $isDataTables = $request->filled('draw') || $request->ajax();
        
        if ($isDataTables) {
            return $this->indexDataTables($request);
        }

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $orders = PurchaseOrder::with(['supplier', 'creator'])
                ->latest()
                ->paginate(PurchaseOrder::count())
                ->appends($request->except('page'));
        } else {
            $orders = PurchaseOrder::with(['supplier', 'creator'])
                ->latest()
                ->paginate($perPage);
        }

        return view('admin.purchaseOrders.index', compact('orders', 'perPage'));
    }

    protected function indexDataTables(\Illuminate\Http\Request $request)
    {
        $query = PurchaseOrder::with(['supplier']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        
        // Siempre permitir ordenamiento
        $orderColumn = (int) $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        // Mapear índice de DataTables al campo de ordenamiento
        // 0: code, 1: supplier (supplier_id), 2: total, 3: status, 4: date_issued
        $columnMap = [
            0 => 'code',           // code
            1 => 'supplier',      // supplier_id - usar join
            2 => 'total',         // total
            3 => 'status',        // status
            4 => 'created_at',  // date_issued -> usar created_at que es más confiable
        ];
        $orderCol = $columnMap[$orderColumn] ?? 'created_at';

        // Ordenar por relación supplier usando join
        try {
            if ($orderCol === 'supplier') {
                $query->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
                      ->orderBy('suppliers.name', $orderDir)
                      ->select('purchase_orders.*');
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } catch (\Exception $e) {
            $query->orderBy('created_at', 'desc');
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")]);
                  });
            });
        }

        $totalRecords = PurchaseOrder::count();
        $totalFiltered = $query->count();

        $orders = $query->offset($start)->limit($length)->get();

        $data = $orders->map(function ($item) {
            $statusLabel = match($item->status) {
                'draft' => 'Borrador',
                'issued' => 'Emitida',
                'completed' => 'Completada',
                'cancelled' => 'Anulada',
                default => ucfirst($item->status)
            };
            $statusClass = match($item->status) {
                'draft' => 'secondary',
                'issued' => 'info',
                'completed' => 'success',
                'cancelled' => 'danger',
                default => 'secondary'
            };
            return [
                'code' => $item->code,
                'supplier' => $item->supplier->name ?? 'N/A',
                'total' => $item->currency . ' ' . number_format($item->total, 2),
                'status' => '<span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span>',
                'date' => $item->date_issued->format('d/m/Y'),
                'actions' => view('admin.purchaseOrders.partials.actions', ['order' => $item])->render(),
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
        $quote = null;
        if ($request->filled('quote')) {
            $quote = PurchaseQuote::with(['supplier', 'items.product'])
                ->where('status', 'approved')
                ->find($request->quote);
        }

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->get();
        $code = PurchaseOrder::generateCode();

        return view('admin.purchaseOrders.create', compact('suppliers', 'products', 'code', 'quote'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date_issued' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $calc = new OrderCalculationService();
            $totals = $calc->calculate($request->items, $request->currency, $request->exchange_rate);

            $order = PurchaseOrder::create([
                'code' => $request->code ?? PurchaseOrder::generateCode(),
                'purchase_quote_id' => $request->purchase_quote_id ?: null,
                'supplier_id' => $request->supplier_id,
                'date_issued' => $request->date_issued,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'currency' => $request->currency,
                'exchange_rate' => $totals['exchange_rate'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'subtotal_bs' => $totals['subtotal_bs'],
                'tax_amount_bs' => $totals['tax_amount_bs'],
                'total_bs' => $totals['total_bs'],
                'terms' => $request->terms,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $productIds = array_column($request->items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                $equivalentBs = $calc->calculateItemEquivalentBs($item['unit_cost'], $request->currency, $request->exchange_rate);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'equivalent_bs' => $equivalentBs * $item['quantity'],
                ]);
            }

            if ($request->purchase_quote_id) {
                PurchaseQuote::where('id', $request->purchase_quote_id)
                    ->update(['status' => 'converted']);
            }

            DB::commit();

            return redirect()->route('admin.purchaseOrders.show', $order)
                ->with('success', 'Orden de compra creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear orden de compra: ' . $e->getMessage());
            return redirect()->route('admin.purchaseOrders.create')
                ->with('error', 'Error al crear la orden. Por favor, intente de nuevo.');
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'creator', 'approver', 'items.product']);

        return view('admin.purchaseOrders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'Solo se pueden editar órdenes en estado borrador.');
        }

        $purchaseOrder->load('items.product');
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->get();

        return view('admin.purchaseOrders.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'Solo se pueden editar órdenes en estado borrador.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date_issued' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchaseOrder->items()->delete();

            $calc = new OrderCalculationService();
            $totals = $calc->calculate($request->items, $request->currency, $request->exchange_rate);

            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'date_issued' => $request->date_issued,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'currency' => $request->currency,
                'exchange_rate' => $totals['exchange_rate'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'subtotal_bs' => $totals['subtotal_bs'],
                'tax_amount_bs' => $totals['tax_amount_bs'],
                'total_bs' => $totals['total_bs'],
                'terms' => $request->terms,
                'notes' => $request->notes,
            ]);

            $productIds = array_column($request->items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                $equivalentBs = $calc->calculateItemEquivalentBs($item['unit_cost'], $request->currency, $request->exchange_rate);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'equivalent_bs' => $equivalentBs * $item['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.purchaseOrders.show', $purchaseOrder)
                ->with('success', 'Orden de compra actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar orden de compra: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la orden. Por favor, intente de nuevo.');
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'Solo se pueden eliminar órdenes en estado borrador.');
        }

        try {
            if ($purchaseOrder->purchase_quote_id) {
                PurchaseQuote::where('id', $purchaseOrder->purchase_quote_id)
                    ->update(['status' => 'approved']);
            }

            $purchaseOrder->delete();

            return redirect()->route('admin.purchaseOrders.index')
                ->with('success', 'Orden de compra eliminada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar orden de compra: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la orden. Por favor, intente de nuevo.');
        }
    }

    public function issue(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeIssued()) {
            return back()->with('error', 'Esta orden no puede ser emitida.');
        }

        $purchaseOrder->update([
            'status' => 'issued',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Orden de compra emitida exitosamente.');
    }

    public function complete(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeCompleted()) {
            return back()->with('error', 'Esta orden no puede ser marcada como completada.');
        }

        if (!$purchaseOrder->isFullyReceived()) {
            return back()->with('error', 'La orden tiene productos pendientes por recibir.');
        }

        $purchaseOrder->update(['status' => 'completed']);

        return back()->with('success', 'Orden de compra completada.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'completed') {
            return back()->with('error', 'No se puede cancelar una orden completada.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return back()->with('success', 'Orden de compra cancelada.');
    }

    public function pdf(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->load(['supplier', 'creator', 'items.product']);

            $pdf = Pdf::loadView('admin.purchaseOrders.pdf', compact('purchaseOrder'));

            return $pdf->stream('OC-' . $purchaseOrder->code . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de orden de compra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF. Por favor, contacte al administrador.');
        }
    }
}
