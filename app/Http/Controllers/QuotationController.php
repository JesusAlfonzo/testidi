<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseQuote;
use App\Models\PurchaseQuoteItem;
use App\Models\Supplier;
use App\Models\Product;
use Carbon\Carbon;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:cotizaciones_ver')->only('index', 'show');
        $this->middleware('permission:cotizaciones_crear')->only('create', 'store');
        $this->middleware('permission:cotizaciones_editar')->only('edit', 'update');
        $this->middleware('permission:cotizaciones_eliminar')->only('destroy');
        $this->middleware('permission:cotizaciones_aprobar')->only('approve');
        $this->middleware('permission:cotizaciones_rechazar')->only('reject');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations = PurchaseQuote::with(['supplier', 'user'])
            ->latest()
            ->paginate(10);
            
        return view('admin.quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();
        
        // Generar código único para la cotización
        $lastQuote = PurchaseQuote::latest('id')->first();
        $code = 'COT-' . date('Y') . '-' . str_pad(($lastQuote ? $lastQuote->id + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        return view('admin.quotations.create', compact('suppliers', 'products', 'code'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_reference' => 'nullable|string|max:255',
            'date_issued' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date_issued',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Calcular totales
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }
            
            $taxAmount = 0; // Por ahora sin impuestos
            $total = $subtotal + $taxAmount;

            // Crear cotización
            $quote = PurchaseQuote::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->user()->id,
                'code' => $request->code,
                'supplier_reference' => $request->supplier_reference,
                'date_issued' => $request->date_issued,
                'valid_until' => $request->valid_until,
                'delivery_date' => $request->delivery_date,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Crear items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                PurchaseQuoteItem::create([
                    'purchase_quote_id' => $quote->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.quotations.show', $quote)
                ->with('success', 'Cotización creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear la cotización: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseQuote $quotation)
    {
        $quotation->load(['supplier', 'user', 'items.product']);
        
        return view('admin.quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseQuote $quotation)
    {
        // Solo permitir editar cotizaciones pendientes
        if ($quotation->status !== 'pending') {
            return back()->with('error', 'Solo se pueden editar cotizaciones con estado pendiente.');
        }
        
        $quotation->load('items');
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();
        
        return view('admin.quotations.edit', compact('quotation', 'suppliers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseQuote $quotation)
    {
        // Solo permitir editar cotizaciones pendientes
        if ($quotation->status !== 'pending') {
            return back()->with('error', 'Solo se pueden editar cotizaciones con estado pendiente.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_reference' => 'nullable|string|max:255',
            'date_issued' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date_issued',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Eliminar items existentes
            $quotation->items()->delete();
            
            // Calcular totales
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }
            
            $taxAmount = 0; // Por ahora sin impuestos
            $total = $subtotal + $taxAmount;

            // Actualizar cotización
            $quotation->update([
                'supplier_id' => $request->supplier_id,
                'supplier_reference' => $request->supplier_reference,
                'date_issued' => $request->date_issued,
                'valid_until' => $request->valid_until,
                'delivery_date' => $request->delivery_date,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Crear nuevos items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                PurchaseQuoteItem::create([
                    'purchase_quote_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.quotations.show', $quotation)
                ->with('success', 'Cotización actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al actualizar la cotización: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseQuote $quotation)
    {
        // Solo permitir eliminar cotizaciones pendientes
        if ($quotation->status !== 'pending') {
            return back()->with('error', 'Solo se pueden eliminar cotizaciones con estado pendiente.');
        }

        try {
            $quotation->delete();
            
            return redirect()->route('admin.quotations.index')
                ->with('success', 'Cotización eliminada exitosamente.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la cotización: ' . $e->getMessage());
        }
    }

    /**
     * Approve the specified quotation.
     */
    public function approve(PurchaseQuote $quotation)
    {
        if ($quotation->status !== 'pending') {
            return back()->with('error', 'Solo se pueden aprobar cotizaciones con estado pendiente.');
        }

        try {
            $quotation->update([
                'status' => 'approved',
            ]);

            return redirect()->route('admin.quotations.show', $quotation)
                ->with('success', 'Cotización aprobada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al aprobar la cotización: ' . $e->getMessage());
        }
    }

    /**
     * Reject the specified quotation.
     */
    public function reject(Request $request, PurchaseQuote $quotation)
    {
        if ($quotation->status !== 'pending') {
            return back()->with('error', 'Solo se pueden rechazar cotizaciones con estado pendiente.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $quotation->update([
                'status' => 'rejected',
                'notes' => ($quotation->notes ?? '') . "\n\nRECHAZADA: " . $request->rejection_reason,
            ]);

            return redirect()->route('admin.quotations.show', $quotation)
                ->with('success', 'Cotización rechazada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al rechazar la cotización: ' . $e->getMessage());
        }
    }
}
