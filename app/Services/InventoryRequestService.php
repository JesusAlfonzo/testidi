<?php
namespace App\Services;

use App\Models\Product;
use App\Models\InventoryRequest;
use App\Models\ProductBatch;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Events\StockUpdated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryRequestService
{
    /**
     * Procesa y genera un despacho para la solicitud de inventario.
     */
    public function dispatch(InventoryRequest $request, array $dispatchData): Dispatch
    {
        return DB::transaction(function () use ($request, $dispatchData) {
            $lockedRequest = InventoryRequest::lockForUpdate()->find($request->id);
            
            if ($lockedRequest->status !== InventoryRequest::STATUS_PENDING) {
                throw new \Exception('La solicitud ya fue procesada o no existe.');
            }

            $itemsData = $dispatchData['items'] ?? $dispatchData;
            $notes = $dispatchData['notes'] ?? null;

            // 1. Validar disponibilidad de stock real para cada producto despachado
            $errors = [];
            foreach ($itemsData as $itemData) {
                $qtyDispatched = (int) $itemData['quantity_dispatched'];
                if ($qtyDispatched > 0) {
                    $product = Product::find($itemData['product_id']);
                    if (!$product) {
                        $errors[] = "Producto ID {$itemData['product_id']} no encontrado.";
                    } elseif (!$product->is_active) {
                        $errors[] = "El producto '{$product->name}' está inactivo.";
                    } elseif ($product->stock < $qtyDispatched) {
                        $errors[] = "Stock insuficiente para '{$product->name}'. Stock actual: {$product->stock}, a despachar: {$qtyDispatched}";
                    }
                }
            }

            if (!empty($errors)) {
                throw new \Exception(implode(' | ', $errors));
            }

            // 2. Crear el Despacho
            $dispatch = Dispatch::create([
                'inventory_request_id' => $request->id,
                'dispatcher_id' => auth()->id() ?? $request->approver_id ?? 1,
                'notes' => $notes,
            ]);

            $totalRequested = 0;
            $totalDispatched = 0;

            // 3. Procesar cada ítem y rebajar stock
            foreach ($itemsData as $itemData) {
                $productId = $itemData['product_id'];
                $qtyRequested = (int) $itemData['quantity_requested'];
                $qtyDispatched = (int) $itemData['quantity_dispatched'];
                
                $totalRequested += $qtyRequested;
                $totalDispatched += $qtyDispatched;

                $product = Product::lockForUpdate()->find($productId);

                if ($qtyDispatched > 0) {
                    // Consumir el stock y obtener información de lotes consumidos
                    $consumedInfo = $product->consumeStock($qtyDispatched, "Despacho {$dispatch->dispatch_number}");
                    
                    // Si el producto es perecedero y tiene lotes consumidos
                    if ($product->shouldUseFifo() && !empty($consumedInfo)) {
                        foreach ($consumedInfo as $consumed) {
                            if (($consumed['type'] ?? '') !== 'simple') {
                                DispatchItem::create([
                                    'dispatch_id' => $dispatch->id,
                                    'product_id' => $productId,
                                    'batch_id' => $consumed['batch_id'] ?? null,
                                    'quantity_requested' => $qtyRequested,
                                    'quantity_dispatched' => $consumed['quantity'],
                                    'status' => 'approved',
                                ]);
                            }
                        }
                    } else {
                        // No perecedero o sin lotes
                        DispatchItem::create([
                            'dispatch_id' => $dispatch->id,
                            'product_id' => $productId,
                            'batch_id' => null,
                            'quantity_requested' => $qtyRequested,
                            'quantity_dispatched' => $qtyDispatched,
                            'status' => 'approved',
                        ]);
                    }

                    // Generar nota para el evento de Kardex
                    $notesEvent = "Despacho {$dispatch->dispatch_number}";
                    if (!empty($consumedInfo) && isset($consumedInfo[0]['batch_number'])) {
                        $notesEvent .= ' | Lotes consumidos: ' . implode(', ', array_column($consumedInfo, 'batch_number'));
                    }

                    // Disparar evento de actualización de stock
                    event(new StockUpdated(
                        product: $product,
                        quantity: $qtyDispatched,
                        type: 'out',
                        referenceId: $dispatch->id,
                        referenceType: Dispatch::class,
                        notes: $notesEvent
                    ));
                } else {
                    // Ítem negado / rechazado (quantity_dispatched = 0)
                    DispatchItem::create([
                        'dispatch_id' => $dispatch->id,
                        'product_id' => $productId,
                        'batch_id' => null,
                        'quantity_requested' => $qtyRequested,
                        'quantity_dispatched' => 0,
                        'status' => 'rejected',
                    ]);
                }
            }

            // 4. Determinar el nuevo estado de la solicitud
            if ($totalDispatched === 0) {
                $lockedRequest->status = InventoryRequest::STATUS_REJECTED;
            } elseif ($totalDispatched >= $totalRequested) {
                $lockedRequest->status = InventoryRequest::STATUS_APPROVED;
            } else {
                $lockedRequest->status = InventoryRequest::STATUS_PARTIALLY_PROCESSED;
            }

            $lockedRequest->approver_id = auth()->id() ?? $request->approver_id ?? 1;
            $lockedRequest->processed_at = Carbon::now();
            $lockedRequest->save();

            return $dispatch;
        });
    }

    /**
     * Aprobación tradicional (completa) por retrocompatibilidad.
     */
    public function approve(InventoryRequest $request): void
    {
        $request->load('items.product');
        
        $dispatchData = [
            'items' => $request->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity_requested' => $item->quantity_requested,
                    'quantity_dispatched' => $item->quantity_requested,
                ];
            })->toArray(),
            'notes' => 'Aprobación automática completa'
        ];

        $this->dispatch($request, $dispatchData);
    }

    /**
     * Rechazo tradicional.
     */
    public function reject(InventoryRequest $request, ?string $reason = null): void
    {
        $request->status = InventoryRequest::STATUS_REJECTED;
        $request->approver_id = auth()->id() ?? 1;
        $request->processed_at = Carbon::now();
        $request->rejection_reason = $reason;
        $request->save();
    }
}
