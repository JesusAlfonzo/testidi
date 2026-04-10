<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PurchaseQuote;
use App\Models\PurchaseQuoteItem;
use App\Models\RequestForQuotation;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\OrderCalculationService;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:cotizaciones_ver')->only(['index', 'show']);
        $this->middleware('can:cotizaciones_crear')->only(['create', 'store']);
        $this->middleware('can:cotizaciones_editar')->only(['edit', 'update']);
        $this->middleware('can:cotizaciones_eliminar')->only(['destroy']);
        $this->middleware('can:cotizaciones_aprobar')->only(['select', 'approve']);
        $this->middleware('can:cotizaciones_rechazar')->only('reject');
    }

    public function index(Request $request)
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

        $query = PurchaseQuote::with(['supplier', 'user', 'rfq']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->rfq_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('view_all') === 'true') {
            $quotations = $query->latest()->paginate($perPage)->appends($request->except('page'));
        } else {
            $quotations = $query->latest()->paginate($perPage)->appends($request->except('per_page'));
        }

        return view('admin.quotations.index', compact('quotations', 'perPage'));
    }

    protected function indexDataTables(Request $request)
    {
        $query = PurchaseQuote::with(['supplier', 'rfq']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->rfq_id);
        }

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
        // 0: code, 1: supplier (supplier_id), 2: rfq (rfq_id), 3: total, 4: status, 5: date_issued
        $columnMap = [
            0 => 'code',           // code
            1 => 'supplier',      // supplier_id - usar join
            2 => 'rfq',         // rfq_id - usar join  
            3 => 'total',         // total
            4 => 'status',       // status
            5 => 'created_at',  // date_issued -> usar created_at
        ];
        $orderCol = $columnMap[$orderColumn] ?? 'created_at';

        // Ordenar por relaciones usando join
        try {
            if ($orderCol === 'supplier') {
                $query->join('suppliers', 'purchase_quotes.supplier_id', '=', 'suppliers.id')
                      ->orderBy('suppliers.name', $orderDir)
                      ->select('purchase_quotes.*');
            } elseif ($orderCol === 'rfq') {
                $query->join('request_for_quotations', 'purchase_quotes.rfq_id', '=', 'request_for_quotations.id')
                      ->orderBy('request_for_quotations.code', $orderDir)
                      ->select('purchase_quotes.*');
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } catch (\Exception $e) {
            $query->orderBy('created_at', 'desc');
        }

        $totalRecords = PurchaseQuote::count();
        $totalFiltered = $query->count();

        $quotations = $query->offset($start)->limit($length)->get();

        $data = $quotations->map(function ($item) {
            $statusLabel = match($item->status) {
                'pending' => 'Pendiente',
                'selected' => 'Seleccionada',
                'approved' => 'Aprobada',
                'rejected' => 'Rechazada',
                'converted' => 'Convertida',
                default => ucfirst($item->status)
            };
            $statusClass = match($item->status) {
                'pending' => 'warning',
                'selected' => 'info',
                'approved' => 'success',
                'rejected' => 'danger',
                'converted' => 'primary',
                default => 'secondary'
            };
            return [
                'code' => $item->code,
                'supplier' => $item->supplier->name ?? 'N/A',
                'rfq' => $item->rfq->code ?? '-',
                'total' => $item->currency . ' ' . number_format($item->total, 2),
                'status' => '<span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span>',
                'date' => $item->date_issued->format('d/m/Y'),
                'actions' => view('admin.quotations.partials.actions', ['quotation' => $item])->render(),
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
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();
        $rfqs = RequestForQuotation::whereIn('status', ['sent', 'closed'])->orderBy('created_at', 'desc')->get();

        $selectedRfq = null;
        if ($request->filled('rfq_id')) {
            $selectedRfq = RequestForQuotation::with('items.product')->find($request->rfq_id);
        }

        $code = PurchaseQuote::generateCode();

        return view('admin.quotations.create', compact('suppliers', 'products', 'code', 'rfqs', 'selectedRfq'));
    }

    public function createFromRfq(RequestForQuotation $rfq)
    {
        if (!in_array($rfq->status, ['sent', 'closed'])) {
            return redirect()->route('admin.rfq.index')
                ->with('error', 'Solo se puede crear cotización desde una RFQ enviada o cerrada.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();
        $rfqs = RequestForQuotation::whereIn('status', ['sent', 'closed'])->orderBy('created_at', 'desc')->get();
        
        $rfq->load('items.product');

        $code = PurchaseQuote::generateCode();
        $selectedRfq = $rfq;

        return view('admin.quotations.create', compact('suppliers', 'products', 'code', 'rfqs', 'selectedRfq'));
    }

    public function store(Request $request)
    {
        $isTempSupplier = $request->filled('supplier_type') && $request->supplier_type === 'temp';

        if ($isTempSupplier) {
            $request->validate([
                'temp_supplier_name' => 'required|string|max:255',
            ], [
                'temp_supplier_name.required' => 'Debe ingresar el nombre del proveedor temporal.',
            ]);
        } else {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
            ], [
                'supplier_id.required' => 'Debe seleccionar un proveedor registrado.',
            ]);
        }

        $request->validate([
            'date_issued' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date_issued',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $calc = new OrderCalculationService();
            $totals = $calc->calculate($request->items, $request->currency, $request->exchange_rate);

            \Log::info('Creating quotation with isTempSupplier: ' . ($isTempSupplier ? 'true' : 'false'));
            \Log::info('Request data: ', $request->all());

            $quoteData = [
                'rfq_id' => $request->rfq_id ?: null,
                'user_id' => auth()->id(),
                'code' => $request->code ?? PurchaseQuote::generateCode(),
                'supplier_reference' => $request->supplier_reference,
                'date_issued' => $request->date_issued,
                'valid_until' => $request->valid_until,
                'delivery_date' => $request->delivery_date,
                'currency' => $request->currency,
                'exchange_rate' => $totals['exchange_rate'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'subtotal_bs' => $totals['subtotal_bs'],
                'tax_amount_bs' => $totals['tax_amount_bs'],
                'total_bs' => $totals['total_bs'],
                'notes' => $request->notes,
                'status' => 'pending',
            ];

            if ($isTempSupplier) {
                $quoteData['supplier_id'] = null;
                $quoteData['supplier_name_temp'] = $request->temp_supplier_name;
                $quoteData['supplier_email_temp'] = $request->temp_supplier_email;
                $quoteData['supplier_phone_temp'] = $request->temp_supplier_phone;
            } else {
                $quoteData['supplier_id'] = $request->supplier_id;
            }

            if ($request->filled('rfq_id')) {
                $quoteData['rfq_id'] = $request->rfq_id;
            }

            \Log::info('Quote data to create: ', $quoteData);

            $quote = PurchaseQuote::create($quoteData);

            \Log::info('Quote created successfully with ID: ' . $quote->id);

            $productIds = array_column($request->items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                $equivalentBs = $calc->calculateItemEquivalentBs($item['unit_cost'], $request->currency, $request->exchange_rate);

                PurchaseQuoteItem::create([
                    'purchase_quote_id' => $quote->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'equivalent_bs' => $equivalentBs * $item['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.quotations.show', ['quotation' => $quote->id])
                ->with('success', 'Cotización registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating quote: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Quote data that failed: ', $quoteData ?? []);
            return back()->withInput()
                ->with('error', 'Error al crear la cotización: ' . $e->getMessage());
        }
    }

    public function show(PurchaseQuote $quotation)
    {
        $quotation->load(['supplier', 'user', 'items.product', 'rfq', 'approver']);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(PurchaseQuote $quotation)
    {
        if (!$quotation->isEditable()) {
            return back()->with('error', 'Solo se pueden editar cotizaciones pendientes.');
        }

        $quotation->load('items.product');
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with(['category', 'unit'])->where('is_active', true)->get();

        return view('admin.quotations.edit', compact('quotation', 'suppliers', 'products'));
    }

    public function update(Request $request, PurchaseQuote $quotation)
    {
        if (!$quotation->isEditable()) {
            return back()->with('error', 'Solo se pueden editar cotizaciones pendientes.');
        }

        $request->validate([
            'supplier_type' => 'required|in:registered,temp',
            'date_issued' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date_issued',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ], [
            'supplier_type.required' => 'Debe seleccionar el tipo de proveedor.',
            'supplier_type.in' => 'El tipo de proveedor debe ser registrado o temporal.',
        ]);

        if ($request->supplier_type === 'registered') {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
            ], [
                'supplier_id.required' => 'Debe seleccionar un proveedor registrado.',
            ]);
        } else {
            $request->validate([
                'supplier_name_temp' => 'required|string|max:255',
            ], [
                'supplier_name_temp.required' => 'Debe ingresar el nombre del proveedor temporal.',
            ]);
        }

        try {
            DB::beginTransaction();

            $quotation->items()->delete();

            $subtotal = 0;
            $subtotalBs = 0;
            $ivaRate = 0.16;
            $isBs = $request->currency === 'Bs';

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_cost'];
                $subtotal += $itemTotal;
                
                if ($isBs) {
                    $equivalentBs = $item['unit_cost'];
                } else {
                    $equivalentBs = $item['unit_cost'] * $request->exchange_rate;
                }
                $subtotalBs += $equivalentBs * $item['quantity'];
            }

            $taxAmountBs = $subtotalBs * $ivaRate;
            $totalBs = $subtotalBs + $taxAmountBs;
            $total = $subtotal;

            $updateData = [
                'supplier_reference' => $request->supplier_reference,
                'date_issued' => $request->date_issued,
                'valid_until' => $request->valid_until,
                'delivery_date' => $request->delivery_date,
                'currency' => $request->currency,
                'exchange_rate' => $isBs ? 1 : $request->exchange_rate,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total' => $total,
                'subtotal_bs' => $subtotalBs,
                'tax_amount_bs' => $taxAmountBs,
                'total_bs' => $totalBs,
                'notes' => $request->notes,
            ];

            if ($request->supplier_type === 'registered') {
                $updateData['supplier_id'] = $request->supplier_id;
                $updateData['supplier_name_temp'] = null;
                $updateData['supplier_email_temp'] = null;
                $updateData['supplier_phone_temp'] = null;
            } else {
                $updateData['supplier_id'] = null;
                $updateData['supplier_name_temp'] = $request->supplier_name_temp;
                $updateData['supplier_email_temp'] = $request->supplier_email_temp;
                $updateData['supplier_phone_temp'] = $request->supplier_phone_temp;
            }

            $quotation->update($updateData);

            $productIds = array_column($request->items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                
                if ($isBs) {
                    $equivalentBs = $item['unit_cost'];
                } else {
                    $equivalentBs = $item['unit_cost'] * $request->exchange_rate;
                }

                PurchaseQuoteItem::create([
                    'purchase_quote_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'equivalent_bs' => $equivalentBs * $item['quantity'],
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

    public function destroy(PurchaseQuote $quotation)
    {
        if (!$quotation->isEditable()) {
            return back()->with('error', 'Solo se pueden eliminar cotizaciones pendientes.');
        }

        try {
            $quotation->delete();

            return redirect()->route('admin.quotations.index')
                ->with('success', 'Cotización eliminada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la cotización: ' . $e->getMessage());
        }
    }

    public function select(PurchaseQuote $quotation)
    {
        if (!$quotation->canBeSelected()) {
            return back()->with('error', 'Esta cotización no puede ser seleccionada.');
        }

        $quotation->update(['status' => 'selected']);

        return back()->with('success', 'Cotización seleccionada para revisión administrativa.');
    }

    public function approve(Request $request, PurchaseQuote $quotation)
    {
        if (!$quotation->canBeApproved()) {
            return back()->with('error', 'Esta cotización no puede ser aprobada.');
        }

        $quotation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', 'Cotización aprobada exitosamente. Ya puede generar la Orden de Compra.');
    }

    public function reject(Request $request, PurchaseQuote $quotation)
    {
        if (!in_array($quotation->status, ['pending', 'selected'])) {
            return back()->with('error', 'Esta cotización no puede ser rechazada.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $quotation->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', 'Cotización rechazada.');
    }

    public function convertToSupplier(Request $request, PurchaseQuote $quotation)
    {
        if ($quotation->hasRegisteredSupplier()) {
            return back()->with('error', 'Esta cotización ya tiene un proveedor registrado.');
        }

        $supplierName = $quotation->supplier_name_temp;
        $taxId = $request->tax_id;

        $existingSupplier = Supplier::where(function ($query) use ($supplierName, $taxId) {
            $query->where('name', $supplierName);
            if ($taxId) {
                $query->orWhere('tax_id', $taxId);
            }
        })->first();

        if ($existingSupplier) {
            $reason = $existingSupplier->name === $supplierName 
                ? 'ya existe un proveedor con el mismo nombre' 
                : 'ya existe un proveedor con el mismo RIF';
            return back()->with('error', "No se puede crear: {$reason}.");
        }

        $request->validate([
            'tax_id' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier = Supplier::create([
            'name' => $supplierName,
            'tax_id' => $taxId,
            'email' => $quotation->supplier_email_temp,
            'phone' => $quotation->supplier_phone_temp,
            'contact_person' => $request->contact_person,
            'address' => $request->address,
            'user_id' => auth()->id(),
        ]);

        $quotation->update([
            'supplier_id' => $supplier->id,
            'supplier_name_temp' => null,
            'supplier_email_temp' => null,
            'supplier_phone_temp' => null,
        ]);

        return back()->with('success', 'Proveedor registrado exitosamente y vinculado a la cotización.');
    }

    public function pdf(PurchaseQuote $quotation)
    {
        try {
            $quotation->load(['supplier', 'user', 'items.product']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.quotations.pdf', compact('quotation'));

            return $pdf->stream('COT-' . $quotation->code . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de cotización: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF. Por favor, contacte al administrador.');
        }
    }
}
