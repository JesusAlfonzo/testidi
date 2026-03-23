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

        $query = RequestForQuotation::with(['creator', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('view_all') === 'true') {
            $rfqs = $query->latest()->paginate(RequestForQuotation::count())->appends($request->except('page'));
        } else {
            $rfqs = $query->latest()->paginate($perPage);
        }

        return view('admin.rfq.index', compact('rfqs', 'perPage'));
    }

    protected function indexDataTables(\Illuminate\Http\Request $request)
    {
        $query = RequestForQuotation::with(['creator', 'items']);

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        $statusSearch = $request->input('columns.status.search.value', '');

        if ($statusSearch) {
            $query->where('status', $statusSearch);
        }

        $orderColumn = $request->input('order.0.column', 4);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['code', 'title', 'status', 'date_required', 'items_count'];
        
        if (isset($columns[$orderColumn])) {
            $orderCol = $columns[$orderColumn];
            if ($orderCol === 'items_count') {
                $query->withCount('items');
                $query->orderBy('items_count', $orderDir);
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
            $statusLabel = match($item->status) {
                'draft' => 'Borrador',
                'sent' => 'Enviada',
                'closed' => 'Cerrada',
                'cancelled' => 'Cancelada',
                default => ucfirst($item->status)
            };
            $statusClass = match($item->status) {
                'draft' => 'secondary',
                'sent' => 'info',
                'closed' => 'success',
                'cancelled' => 'danger',
                default => 'secondary'
            };
            return [
                'code' => $item->code,
                'title' => $item->title,
                'status' => '<span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span>',
                'date_required' => $item->date_required ? $item->date_required->format('d/m/Y') : '-',
                'items_count' => '<span class="badge badge-info">' . $item->items->count() . '</span>',
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
