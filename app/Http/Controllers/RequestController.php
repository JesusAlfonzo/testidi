<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as RequestModel; // 🔑 ACTUALIZADO: Referencia al modelo renombrado
use App\Models\Product;
use App\Models\RequestItem;
use App\Http\Requests\StoreRequestRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request as HttpRequest; // Clase base de Request de Laravel
use Carbon\Carbon;

class RequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:solicitudes_ver')->only('index', 'show');
        $this->middleware('permission:solicitudes_crear')->only('create', 'store');
        $this->middleware('permission:solicitudes_aprobar')->only('process');
    }

    public function index()
    {
        // Usamos RequestModel
        $requests = RequestModel::with(['requester', 'approver', 'items.product'])
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        return view('admin.requests.index', compact('requests'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->pluck('name', 'id');
        return view('admin.requests.create', compact('products'));
    }

    // Lógica: Guardar la solicitud y sus items
    public function store(StoreRequestRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // 1. Crear la cabecera de la solicitud
            $requestModel = RequestModel::create([ // Usamos RequestModel
                'requester_id' => auth()->id(),
                'status' => 'Pending',
                'justification' => $validatedData['justification'],
                'requested_at' => Carbon::now(),
            ]);

            // 2. Crear los ítems de la solicitud
            $itemsToStore = [];
            foreach ($validatedData['items'] as $item) {

                $product = Product::find($item['product_id']);
                $unitPrice = $product->cost ?? 0;

                $itemsToStore[] = new RequestItem([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                    'unit_price_at_request' => $unitPrice,
                ]);
            }

            $requestModel->items()->saveMany($itemsToStore);

            DB::commit();
            return redirect()->route('admin.requests.show', $requestModel)
                ->with('success', '✅ Solicitud enviada para aprobación: REQ-' . $requestModel->id);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', '❌ Error al guardar la solicitud: ' . $e->getMessage());
        }
    }

    // Muestra los detalles de la solicitud
    public function show(RequestModel $request)
    {
        $request->load([
            'requester',
            'approver',
            'items.product.unit',
        ]);

        return view('admin.requests.show', compact('request'));
    }

    // Método especializado para APROBAR o RECHAZAR una solicitud
    // 🔑 OPTIMIZACIÓN: El parámetro de Route Model Binding ahora es $request
    public function process(HttpRequest $httpRequest, RequestModel $request)
    {
        if (!$request || $request->status !== 'Pending') {
            return redirect()->back()->with('error', '❌ Esta solicitud ya fue procesada o no existe.');
        }

        $action = $httpRequest->input('action');
        $reason = $httpRequest->input('rejection_reason');

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $request->status = 'Approved';
                $request->approver_id = auth()->id();
                $request->processed_at = Carbon::now();

                $request->load('items.product');

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);

                    if (!$product || $product->stock < $item->quantity_requested) {
                         DB::rollback();
                         $productName = $product->name ?? 'ID:' . $item->product_id;
                         return redirect()->route('admin.requests.index')
                             ->with('error', '⚠️ Stock insuficiente o producto no encontrado: ' . $productName . '. Solicitud no aprobada.');
                    }

                    $product->stock -= $item->quantity_requested;
                    $product->save();
                }

                $request->save();
                DB::commit();
                return redirect()->route('admin.requests.index')->with('success', '✅ Solicitud APROBADA y stock actualizado correctamente.');
            } elseif ($action === 'reject') {
                $request->status = 'Rejected';
                $request->approver_id = auth()->id();
                $request->processed_at = Carbon::now();
                $request->rejection_reason = $reason;
                $request->save();

                DB::commit();
                return redirect()->route('admin.requests.index')->with('warning', '🛑 Solicitud RECHAZADA.');
            } else {
                DB::rollback();
                return redirect()->back()->with('error', 'Acción no válida.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', '❌ Error de Transacción: ' . $e->getMessage());
        }
    }

    // Métodos no implementados
    public function destroy($id)
    {
        return abort(404, 'La solicitud no puede ser eliminada, solo rechazada.');
    }
    public function edit($id)
    {
        return abort(404);
    }
    public function update(HttpRequest $request, $id)
    {
        return abort(404);
    }
}
