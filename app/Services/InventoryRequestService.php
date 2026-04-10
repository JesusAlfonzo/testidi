<?php
namespace App\Services;

use App\Models\Product;
use App\Models\InventoryRequest;
use App\Models\ProductBatch;
use App\Events\StockUpdated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryRequestService
{
    public function approve(InventoryRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $lockedRequest = InventoryRequest::lockForUpdate()->find($request->id);
            
            if ($lockedRequest->status !== 'Pending') {
                throw new \Exception('La solicitud ya fue procesada o no existe.');
            }

            $request->load('items.product', 'items.kit.components');

            $this->validateStockAvailability($request);

            $request->status = 'Approved';
            $request->approver_id = auth()->id();
            $request->processed_at = Carbon::now();

            foreach ($request->items as $item) {
                if ($item->item_type === 'product') {
                    $this->approveProductItem($item);
                } elseif ($item->item_type === 'kit') {
                    $this->approveKitItem($item);
                }
            }

            $request->save();
        });
    }

    private function validateStockAvailability(InventoryRequest $request): void
    {
        $errors = [];

        foreach ($request->items as $item) {
            if ($item->item_type === 'product') {
                $product = Product::find($item->product_id);
                if (!$product) {
                    $errors[] = "Producto ID {$item->product_id} no encontrado.";
                } elseif (!$product->is_active) {
                    $errors[] = "El producto '{$product->name}' está inactivo.";
                } elseif ($product->stock < $item->quantity_requested) {
                    $errors[] = "Stock insuficiente para '{$product->name}'. Stock actual: {$product->stock}, solicitado: {$item->quantity_requested}";
                }
            } elseif ($item->item_type === 'kit') {
                $kit = $item->kit;
                if (!$kit) {
                    $errors[] = "Kit ID {$item->kit_id} no encontrado.";
                } elseif (!$kit->is_active) {
                    $errors[] = "El kit '{$kit->name}' está inactivo.";
                } else {
                    foreach ($kit->components as $component) {
                        $required = $component->pivot->quantity_required * $item->quantity_requested;
                        if ($component->stock < $required) {
                            $errors[] = "Stock insuficiente para componente '{$component->name}' del Kit '{$kit->name}'. Stock actual: {$component->stock}, requerido: {$required}";
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception(implode(' | ', $errors));
        }
    }

    public function reject(InventoryRequest $request, string $reason): void
    {
        $request->status = 'Rejected';
        $request->approver_id = auth()->id();
        $request->processed_at = Carbon::now();
        $request->rejection_reason = $reason;
        $request->save();
    }

    private function approveProductItem($item): void
    {
        $product = Product::lockForUpdate()->find($item->product_id);

        if (!$product) {
            throw new \Exception('Producto no encontrado con ID: ' . $item->product_id);
        }

        if (!$product->is_active) {
            throw new \Exception('El producto "' . $product->name . '" está inactivo y no puede ser aprobado.');
        }

        if ($product->stock < $item->quantity_requested) {
            throw new \Exception('Stock insuficiente para el producto: ' . $product->name);
        }

        $consumedInfo = $product->consumeStock($item->quantity_requested, 'Solicitud de salida aprobada');
        $notes = 'Solicitud REQ-' . $item->request_id . ' aprobada';

        if (!empty($consumedInfo) && isset($consumedInfo[0]['batch_number'])) {
            $notes .= ' | Lotes consumidos: ' . implode(', ', array_column($consumedInfo, 'batch_number'));
        }

        event(new StockUpdated(
            product: $product,
            quantity: $item->quantity_requested,
            type: 'out',
            referenceId: $item->request_id,
            referenceType: InventoryRequest::class,
            notes: $notes
        ));
    }

    private function approveKitItem($item): void
    {
        $kit = $item->kit;
        $qtyKit = $item->quantity_requested;
        
        if (!$kit) {
            throw new \Exception("Kit ID {$item->kit_id} no encontrado.");
        }

        if (!$kit->is_active) {
            throw new \Exception("El kit '{$kit->name}' está inactivo y no puede ser aprobado.");
        }

        foreach ($kit->components as $component) {
            $totalConsumption = $qtyKit * $component->pivot->quantity_required;

            $prodComponent = Product::lockForUpdate()->find($component->id);
            
            if (!$prodComponent) {
                throw new \Exception("Componente con ID {$component->id} no encontrado.");
            }

            if (!$prodComponent->is_active) {
                throw new \Exception("El producto componente '{$prodComponent->name}' está inactivo.");
            }
            
            if ($prodComponent->stock < $totalConsumption) {
                throw new \Exception("Stock insuficiente para componente '{$component->name}' del Kit '{$kit->name}'.");
            }
            
            $consumedInfo = $prodComponent->consumeStock($totalConsumption, "Salida por Kit: {$kit->name}");
            $notes = "Salida por Kit: {$kit->name} | REQ-{$item->request_id}";

            if (!empty($consumedInfo) && isset($consumedInfo[0]['batch_number'])) {
                $notes .= ' | Lotes: ' . implode(', ', array_column($consumedInfo, 'batch_number'));
            }

            event(new StockUpdated(
                product: $prodComponent,
                quantity: $totalConsumption,
                type: 'out',
                referenceId: $item->request_id,
                referenceType: InventoryRequest::class,
                notes: $notes
            ));
        }
    }
}
