<?php

namespace App\Http\Controllers;

use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\Product;
use App\Models\RfqSupplierOffer;
use App\Models\RfqSupplierOfferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RequestForQuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:rfq_ver')->only(['index']);
        $this->middleware('can:rfq_crear')->only(['create', 'store']);
        $this->middleware('can:rfq_editar')->only(['edit', 'update']);
        $this->middleware('can:rfq_eliminar')->only(['destroy']);
        $this->middleware('can:rfq_enviar')->only(['markAsSent', 'markAsClosed', 'cancel']);
    }

    public function index(\Illuminate\Http\Request $request)
    {
        if ($request->ajax()) {
            return $this->indexDataTables($request);
        }

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $query = RequestForQuotation::with(['creator', 'items.product', 'purchaseOrder'])
            ->withCount('supplierOffers');

        if ($request->filled('status')) {
            $statusSearch = $request->status;
            if ($statusSearch === 'po') {
                $query->has('purchaseOrder');
            } elseif (in_array($statusSearch, ['sent', 'closed'])) {
                $query->where('status', $statusSearch)->doesntHave('purchaseOrder');
            } else {
                $query->where('status', $statusSearch);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->get('view_all') === 'true') {
            $rfqs = $query->latest()->paginate($perPage)->appends($request->except('page'));
        } else {
            $rfqs = $query->latest()->paginate($perPage);
        }

        return view('admin.rfq.index', compact('rfqs', 'perPage'));
    }

    protected function indexDataTables(\Illuminate\Http\Request $request)
    {
        $query = RequestForQuotation::with(['creator', 'items.product.unit', 'purchaseOrder'])
            ->withCount('supplierOffers');

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        
        $statusSearch = $request->input('status');
        $prioritySearch = $request->input('priority');

        if ($statusSearch) {
            if ($statusSearch === 'po') {
                $query->has('purchaseOrder');
            } elseif (in_array($statusSearch, ['sent', 'closed'])) {
                $query->where('status', $statusSearch)->doesntHave('purchaseOrder');
            } else {
                $query->where('status', $statusSearch);
            }
        }

        if ($prioritySearch) {
            $query->where('priority', $prioritySearch);
        }

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['expand_btn', 'code', 'title', 'status', 'priority', 'date_required', 'supplier_offers_count'];
        
        if (isset($columns[$orderColumn]) && $columns[$orderColumn] !== 'expand_btn') {
            $orderCol = $columns[$orderColumn];
            if ($orderCol === 'supplier_offers_count') {
                $query->orderBy('supplier_offers_count', $orderDir);
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereRaw('LOWER(title) LIKE ?', [strtolower("%{$search}%")]);
            });
        }

        $totalRecords = RequestForQuotation::count();
        $totalFiltered = $query->count();

        $rfqs = $query->offset($start)->limit($length)->get();

        $data = $rfqs->map(function ($item) {
            $hasPO = $item->purchaseOrder !== null;
            
            $statusLabel = 'Borrador';
            $statusClass = 'secondary';
            
            if ($hasPO) {
                $statusLabel = 'Convertida a PO';
                $statusClass = 'success';
            } elseif ($item->status === 'closed') {
                $statusLabel = 'Cotizada';
                $statusClass = 'purple';
            } elseif ($item->status === 'sent') {
                $statusLabel = 'Enviada';
                $statusClass = 'warning text-dark';
            } elseif ($item->status === 'cancelled') {
                $statusLabel = 'Cancelada';
                $statusClass = 'danger';
            }

            $priorityLabel = match($item->priority) {
                'alta' => 'Alta',
                'media' => 'Media',
                'baja' => 'Baja',
                default => 'Baja'
            };
            $priorityClass = match($item->priority) {
                'alta' => 'danger-light',
                'media' => 'info',
                'baja' => 'secondary',
                default => 'secondary'
            };

            $offersLabel = $item->supplier_offers_count > 0 
                ? '<span class="badge badge-pill badge-info py-1 px-2 font-weight-bold shadow-sm"><i class="fas fa-file-invoice-dollar mr-1"></i> ' . $item->supplier_offers_count . ' ' . ($item->supplier_offers_count == 1 ? 'Oferta' : 'Ofertas') . '</span>'
                : '<span class="text-muted text-xs">Sin ofertas</span>';

            return [
                'expand_btn' => '<button class="btn btn-xs btn-outline-primary toggle-child-row" title="Ver productos"><i class="fas fa-plus"></i></button>',
                'code' => $item->code,
                'title' => $item->title,
                'status' => '<span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span>',
                'priority' => '<span class="badge badge-' . $priorityClass . '">' . $priorityLabel . '</span>',
                'date_required' => $item->date_required ? $item->date_required->format('d/m/Y') : '-',
                'supplier_offers_count' => $offersLabel,
                'items_list' => $item->items->map(function($rfqItem) {
                    return [
                        'code' => $rfqItem->product->code ?? 'S/C',
                        'name' => $rfqItem->product->name ?? 'Producto',
                        'quantity' => $rfqItem->quantity,
                        'unit' => $rfqItem->product->unit->abbreviation ?? 'und'
                    ];
                }),
                'actions' => view('admin.rfq.partials.actions', ['rfq' => $item])->render(),
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
        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $kits = \App\Models\Kit::where('is_active', true)
            ->orderBy('name')
            ->get();

        $code = RequestForQuotation::generateCode();

        return view('admin.rfq.create', compact('products', 'kits', 'code'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_required' => 'nullable|date',
            'delivery_deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,kit',
            'items.*.product_id' => 'required_if:items.*.item_type,product|nullable|exists:products,id',
            'items.*.kit_id' => 'required_if:items.*.item_type,kit|nullable|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'priority' => 'nullable|string|in:baja,media,alta',
        ]);

        try {
            DB::beginTransaction();

            $rfq = RequestForQuotation::create([
                'code' => $request->code ?? RequestForQuotation::generateCode(),
                'title' => $request->title,
                'description' => $request->description,
                'date_required' => $request->date_required,
                'delivery_deadline' => $request->delivery_deadline,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => auth()->id(),
                'priority' => $request->priority ?? 'baja',
            ]);

            foreach ($request->items as $item) {
                $productId = $item['item_type'] === 'kit' ? $item['kit_id'] : $item['product_id'];
                RfqItem::create([
                    'rfq_id' => $rfq->id,
                    'item_type' => 'product',
                    'product_id' => $productId,
                    'kit_id' => null,
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.rfq.show', $rfq)
                ->with('success', 'Solicitud de cotización creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al crear RFQ: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error al crear la solicitud. Por favor, intente de nuevo.');
        }
    }

    public function show(RequestForQuotation $rfq)
    {
        $rfq->load(['creator', 'items.product.category', 'items.product.unit', 'items.kit', 'supplierOffers.items']);
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('admin.rfq.show', compact('rfq', 'suppliers'));
    }

    public function edit(RequestForQuotation $rfq)
    {
        if (!$rfq->isEditable()) {
            return back()->with('error', 'Solo se pueden editar solicitudes en estado borrador.');
        }

        $rfq->load('items.product', 'items.kit');
        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $kits = \App\Models\Kit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.rfq.edit', compact('rfq', 'products', 'kits'));
    }

    public function update(Request $request, RequestForQuotation $rfq)
    {
        if (!$rfq->isEditable()) {
            return back()->with('error', 'Solo se pueden editar solicitudes en estado borrador.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_required' => 'nullable|date',
            'delivery_deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,kit',
            'items.*.product_id' => 'required_if:items.*.item_type,product|nullable|exists:products,id',
            'items.*.kit_id' => 'required_if:items.*.item_type,kit|nullable|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'priority' => 'nullable|string|in:baja,media,alta',
        ]);

        try {
            DB::beginTransaction();

            $rfq->update([
                'title' => $request->title,
                'description' => $request->description,
                'date_required' => $request->date_required,
                'delivery_deadline' => $request->delivery_deadline,
                'notes' => $request->notes,
                'priority' => $request->priority ?? 'baja',
            ]);

            $rfq->items()->delete();

            foreach ($request->items as $item) {
                $productId = $item['item_type'] === 'kit' ? $item['kit_id'] : $item['product_id'];
                RfqItem::create([
                    'rfq_id' => $rfq->id,
                    'item_type' => 'product',
                    'product_id' => $productId,
                    'kit_id' => null,
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.rfq.show', $rfq)
                ->with('success', 'Solicitud de cotización actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al actualizar RFQ: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error al actualizar la solicitud. Por favor, intente de nuevo.');
        }
    }

    public function destroy(RequestForQuotation $rfq)
    {
        if (!$rfq->isEditable()) {
            return back()->with('error', 'Solo se pueden eliminar solicitudes en estado borrador.');
        }

        try {
            $rfq->delete();
            return redirect()->route('admin.rfq.index')
                ->with('success', 'Solicitud de cotización eliminada exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar RFQ: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la solicitud. Por favor, intente de nuevo.');
        }
    }

    public function markAsSent(RequestForQuotation $rfq)
    {
        if ($rfq->status !== 'draft') {
            return back()->with('error', 'Solo se pueden enviar solicitudes en estado borrador.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rfq) {
                $rfq->update(['status' => 'sent']);
            });
            return back()->with('success', 'Solicitud marcada como enviada.');
        } catch (\Exception $e) {
            \Log::error('Error al enviar RFQ: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar la solicitud. Por favor, intente de nuevo.');
        }
    }

    public function markAsClosed(RequestForQuotation $rfq)
    {
        if ($rfq->status !== 'sent') {
            return back()->with('error', 'Solo se pueden cerrar solicitudes enviadas.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rfq) {
                $rfq->update(['status' => 'closed']);
            });
            return back()->with('success', 'Solicitud cerrada exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al cerrar RFQ: ' . $e->getMessage());
            return back()->with('error', 'Error al cerrar la solicitud. Por favor, intente de nuevo.');
        }
    }

    public function cancel(RequestForQuotation $rfq)
    {
        if (!in_array($rfq->status, ['draft', 'sent'])) {
            return back()->with('error', 'Solo se pueden cancelar solicitudes en borrador o enviadas.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rfq) {
                $rfq->update(['status' => 'cancelled']);
            });
            return back()->with('success', 'Solicitud cancelada.');
        } catch (\Exception $e) {
            \Log::error('Error al cancelar RFQ: ' . $e->getMessage());
            return back()->with('error', 'Error al cancelar la solicitud. Por favor, intente de nuevo.');
        }
    }

    public function pdf(RequestForQuotation $rfq)
    {
        try {
            $rfq->load(['creator', 'items.product.category', 'items.product.unit', 'items.kit']);

            $pdf = Pdf::loadView('admin.rfq.pdf', compact('rfq'));

            return $pdf->stream('RFQ-' . $rfq->code . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de RFQ: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF. Por favor, contacte al administrador.');
        }
    }

    public function convertToPO(RequestForQuotation $rfq, Request $request)
    {
        if (!$rfq->canConvertToPO()) {
            return back()->with('error', 'Esta RFQ no puede convertirse a Orden de Compra.');
        }

        $rfq->load('items.product', 'items.kit');
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $products = \App\Models\Product::with(['category', 'unit'])->get();
        $kits = \App\Models\Kit::where('is_active', true)->orderBy('name')->get();
        $code = \App\Models\PurchaseOrder::generateCode();

        $offer = null;
        if ($request->has('rfq_supplier_offer_id')) {
            $offer = RfqSupplierOffer::with('items')->find($request->rfq_supplier_offer_id);
        }

        return view('admin.rfq.convert-to-po', compact('rfq', 'suppliers', 'products', 'kits', 'code', 'offer'));
    }

    public function saveSupplierOffer(Request $request, RequestForQuotation $rfq)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.currency' => 'required|string|in:USD,EUR,Bs',
            'items.*.tax_status' => 'required|string|in:exento,gravado',
        ]);

        try {
            DB::beginTransaction();

            $offer = RfqSupplierOffer::updateOrCreate(
                [
                    'rfq_id' => $rfq->id,
                    'supplier_id' => $request->supplier_id,
                ],
                [
                    'notes' => $request->notes,
                ]
            );

            $offer->items()->delete();

            foreach ($request->items as $item) {
                RfqSupplierOfferItem::create([
                    'rfq_supplier_offer_id' => $offer->id,
                    'product_id' => $item['product_id'],
                    'unit_price' => $item['unit_price'],
                    'currency' => $item['currency'],
                    'tax_status' => $item['tax_status'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oferta guardada exitosamente.',
                'offer_id' => $offer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al guardar oferta de proveedor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storePOFromRFQ(Request $request, RequestForQuotation $rfq)
    {
        if (!$rfq->canConvertToPO()) {
            return back()->with('error', 'Esta RFQ no puede convertirse a Orden de Compra.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date_issued' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,kit',
            'items.*.product_id' => 'required_if:items.*.item_type,product|nullable|exists:products,id',
            'items.*.kit_id' => 'required_if:items.*.item_type,kit|nullable|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $calc = new \App\Services\OrderCalculationService();
            $totals = $calc->calculate($request->items, $request->currency, $request->exchange_rate, $request->boolean('iva_exempt'));

            $order = \App\Models\PurchaseOrder::create([
                'code' => $request->code ?? \App\Models\PurchaseOrder::generateCode(),
                'rfq_id' => $rfq->id,
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
                'notes' => 'Generado desde RFQ-' . $rfq->code . '. ' . ($request->notes ?? ''),
                'status' => 'draft',
                'created_by' => auth()->id(),
                'iva_exempt' => $request->boolean('iva_exempt'),
            ]);

            $productIds = [];
            foreach ($request->items as $item) {
                $productIds[] = $item['item_type'] === 'kit' ? $item['kit_id'] : $item['product_id'];
            }
            $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $productId = $item['item_type'] === 'kit' ? $item['kit_id'] : $item['product_id'];
                $equivalentBs = $calc->calculateItemEquivalentBs($item['unit_cost'], $request->currency, $request->exchange_rate);

                $product = $products->get($productId);
                $product_name = $product ? $product->name : '';
                $product_code = $product ? $product->code : '';

                \App\Models\PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'item_type' => 'product',
                    'product_id' => $productId,
                    'kit_id' => null,
                    'product_name' => $product_name,
                    'product_code' => $product_code,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'equivalent_bs' => $equivalentBs * $item['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.purchaseOrders.show', $order)
                ->with('success', 'Orden de Compra creada exitosamente desde la RFQ.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear OC desde RFQ: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear la orden. Por favor, intente de nuevo.');
        }
    }
}
