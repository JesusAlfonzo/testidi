<?php
namespace App\Services;

use App\Models\Product;
use App\Models\InventoryRequest;
use App\Events\StockUpdated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryRequestService
{
    public function approve(InventoryRequest $request): void
    {
        $request->status = 'Approved';
        $request->approver_id = auth()->id();
        $request->processed_at = Carbon::now();

        $request->load('items.product', 'items.kit.components');

        foreach ($request->items as $item) {
            if ($item->item_type === 'product') {
                $this->approveProductItem($item);
            } elseif ($item->item_type === 'kit') {
                $this->approveKitItem($item);
            }
        }

        $request->save();
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

        if (!$product || $product->stock < $item->quantity_requested) {
            throw new \Exception('Stock insuficiente para el producto: ' . ($product->name ?? 'Desconocido'));
        }

        $product->stock -= $item->quantity_requested;
        $product->save();

        event(new StockUpdated(
            product: $product,
            quantity: $item->quantity_requested,
            type: 'out',
            referenceId: $item->request_id,
            referenceType: InventoryRequest::class,
            notes: 'Solicitud de salida aprobada'
        ));
    }

    private function approveKitItem($item): void
    {
        $kit = $item->kit;
        $qtyKit = $item->quantity_requested;
        
        if (!$kit) {
            throw new \Exception("Kit ID {$item->kit_id} no encontrado.");
        }

        foreach ($kit->components as $component) {
            $totalConsumption = $qtyKit * $component->pivot->quantity_required;

            $prodComponent = Product::lockForUpdate()->find($component->id);
            
            if (!$prodComponent || $prodComponent->stock < $totalConsumption) {
                throw new \Exception("Stock insuficiente para componente '{$component->name}' del Kit '{$kit->name}'.");
            }
            
            $prodComponent->stock -= $totalConsumption;
            $prodComponent->save();

            event(new StockUpdated(
                product: $prodComponent,
                quantity: $totalConsumption,
                type: 'out',
                referenceId: $item->request_id,
                referenceType: InventoryRequest::class,
                notes: "Salida por Kit: {$kit->name}"
            ));
        }
    }
}
