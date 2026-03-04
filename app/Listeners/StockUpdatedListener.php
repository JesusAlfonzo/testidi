<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\Models\Activity;

class StockUpdatedListener
{
    public function handle(StockUpdated $event): void
    {
        $typeLabel = match ($event->type) {
            'in' => 'Entrada de stock',
            'out' => 'Salida de stock',
            'adjustment' => 'Ajuste de stock',
            default => 'Movimiento de stock',
        };

        $quantitySign = $event->type === 'in' ? '+' : '-';

        $description = sprintf(
            '%s: %s%d unidades. Stock anterior: %d, Stock nuevo: %d',
            $typeLabel,
            $quantitySign,
            $event->quantity,
            $event->product->stock - ($event->type === 'in' ? $event->quantity : -$event->quantity),
            $event->product->stock
        );

        if ($event->notes) {
            $description .= '. Nota: ' . $event->notes;
        }

        activity()
            ->on($event->product)
            ->withProperties([
                'quantity' => $event->quantity,
                'type' => $event->type,
                'stock_before' => $event->product->stock - ($event->type === 'in' ? $event->quantity : -$event->quantity),
                'stock_after' => $event->product->stock,
                'reference_id' => $event->referenceId,
                'reference_type' => $event->referenceType,
            ])
            ->log($description);
    }
}
