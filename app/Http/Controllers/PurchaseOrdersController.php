<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseQuote;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ordenes_compra_ver')->only(['index', 'show']);
        $this->middleware('permission:ordenes_compra_crear')->only(['create', 'store']);
        $this->middleware('permission:ordenes_compra_editar')->only(['edit', 'update']);
        $this->middleware('permission:ordenes_compra_eliminar')->only(['destroy']);
        $this->middleware('permission:ordenes_compra_aprobar')->only(['issue', 'complete']);
        $this->middleware('permission:ordenes_compra_anular')->only(['cancel']);
    }

    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'creator'])
            ->latest()
            ->get();

        return view('admin.purchaseOrders.index', compact('orders'));
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
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();
        $code = PurchaseOrder::generateCode();

        return view('admin.purchaseOrders.create', compact('suppliers', 'products', 'code', 'quote'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date_issued' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }

            $taxAmount = 0;
            $total = $subtotal + $taxAmount;

            $order = PurchaseOrder::create([
                'code' => $request->code ?? PurchaseOrder::generateCode(),
                'purchase_quote_id' => $request->purchase_quote_id ?: null,
                'supplier_id' => $request->supplier_id,
                'date_issued' => $request->date_issued,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'terms' => $request->terms,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            if ($request->purchase_quote_id) {
                PurchaseQuote::where('id', $request->purchase_quote_id)
                    ->update(['status' => 'converted']);
            }

            DB::commit();

            return redirect()->route('admin.purchaseOrders.show', $order)
                ->with('success', 'Orden de compra creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear la orden: ' . $e->getMessage());
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
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();

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
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchaseOrder->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }

            $taxAmount = 0;
            $total = $subtotal + $taxAmount;

            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'date_issued' => $request->date_issued,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'terms' => $request->terms,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.purchaseOrders.show', $purchaseOrder)
                ->with('success', 'Orden de compra actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al actualizar la orden: ' . $e->getMessage());
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
            return back()->with('error', 'Error al eliminar la orden: ' . $e->getMessage());
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
        $purchaseOrder->load(['supplier', 'creator', 'items.product']);

        $pdf = Pdf::loadView('admin.purchaseOrders.pdf', compact('purchaseOrder'));

        return $pdf->stream('OC-' . $purchaseOrder->code . '.pdf');
    }
}
