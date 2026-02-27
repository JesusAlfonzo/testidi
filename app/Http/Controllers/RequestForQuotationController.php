<?php

namespace App\Http\Controllers;

use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RequestForQuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rfq_ver')->only(['index', 'show']);
        $this->middleware('permission:rfq_crear')->only(['create', 'store']);
        $this->middleware('permission:rfq_editar')->only(['edit', 'update']);
        $this->middleware('permission:rfq_eliminar')->only(['destroy']);
        $this->middleware('permission:rfq_enviar')->only(['markAsSent', 'markAsClosed', 'cancel']);
    }

    public function index()
    {
        $rfqs = RequestForQuotation::with(['creator', 'items.product'])
            ->latest()
            ->get();

        return view('admin.rfq.index', compact('rfqs'));
    }

    public function create()
    {
        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $code = RequestForQuotation::generateCode();

        return view('admin.rfq.create', compact('products', 'code'));
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
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
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
            ]);

            foreach ($request->items as $item) {
                RfqItem::create([
                    'rfq_id' => $rfq->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.rfq.show', $rfq)
                ->with('success', 'Solicitud de cotización creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear la solicitud: ' . $e->getMessage());
        }
    }

    public function show(RequestForQuotation $rfq)
    {
        $rfq->load(['creator', 'items.product.category', 'items.product.unit']);

        return view('admin.rfq.show', compact('rfq'));
    }

    public function edit(RequestForQuotation $rfq)
    {
        if (!$rfq->isEditable()) {
            return back()->with('error', 'Solo se pueden editar solicitudes en estado borrador.');
        }

        $rfq->load('items.product');
        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.rfq.edit', compact('rfq', 'products'));
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
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $rfq->update([
                'title' => $request->title,
                'description' => $request->description,
                'date_required' => $request->date_required,
                'delivery_deadline' => $request->delivery_deadline,
                'notes' => $request->notes,
            ]);

            $rfq->items()->delete();

            foreach ($request->items as $item) {
                RfqItem::create([
                    'rfq_id' => $rfq->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.rfq.show', $rfq)
                ->with('success', 'Solicitud de cotización actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al actualizar la solicitud: ' . $e->getMessage());
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
            return back()->with('error', 'Error al eliminar la solicitud: ' . $e->getMessage());
        }
    }

    public function markAsSent(RequestForQuotation $rfq)
    {
        if ($rfq->status !== 'draft') {
            return back()->with('error', 'Solo se pueden enviar solicitudes en estado borrador.');
        }

        $rfq->update(['status' => 'sent']);
        return back()->with('success', 'Solicitud marcada como enviada.');
    }

    public function markAsClosed(RequestForQuotation $rfq)
    {
        if ($rfq->status !== 'sent') {
            return back()->with('error', 'Solo se pueden cerrar solicitudes enviadas.');
        }

        $rfq->update(['status' => 'closed']);
        return back()->with('success', 'Solicitud cerrada exitosamente.');
    }

    public function cancel(RequestForQuotation $rfq)
    {
        if (!in_array($rfq->status, ['draft', 'sent'])) {
            return back()->with('error', 'Solo se pueden cancelar solicitudes en borrador o enviadas.');
        }

        $rfq->update(['status' => 'cancelled']);
        return back()->with('success', 'Solicitud cancelada.');
    }

    public function pdf(RequestForQuotation $rfq)
    {
        $rfq->load(['creator', 'items.product.category', 'items.product.unit']);

        $pdf = Pdf::loadView('admin.rfq.pdf', compact('rfq'));
        
        return $pdf->stream('RFQ-' . $rfq->code . '.pdf');
    }
}
