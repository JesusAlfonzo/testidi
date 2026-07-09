<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'item_type',
        'product_id',
        'uom_id',
        'kit_id',
        'product_name',
        'product_code',
        'quantity',
        'quantity_uom',
        'quantity_received',
        'quantity_rejected',
        'quantity_replaced',
        'unit_cost',
        'unit_cost_uom',
        'total_cost',
        'equivalent_bs',
        'is_exempt',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'equivalent_bs' => 'decimal:2',
        'is_exempt' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function stockInItems()
    {
        return $this->hasMany(StockInItem::class, 'purchase_order_item_id');
    }

    public function getPendingQuantity(): int
    {
        return $this->quantity - $this->quantity_received - $this->quantity_replaced;
    }

    public function getRejectedPendingQuantity(): int
    {
        return $this->quantity_rejected - $this->quantity_replaced;
    }

    public function isFullyReceived(): bool
    {
        return ($this->quantity_received + $this->quantity_replaced) >= $this->quantity;
    }

    public function recalculateKitQuantities()
    {
        if ($this->item_type !== 'kit') {
            return;
        }

        $this->load(['kit.components', 'stockInItems']);

        if (!$this->kit) {
            return;
        }

        // Si el kit fue recibido directamente como unidad sellada
        $unifiedProduct = Product::where('type', 'composite_kit')
            ->where(function ($q) {
                $q->where('code', 'KIT-' . $this->kit_id)
                  ->orWhere('name', $this->product_name);
            })->first();

        if ($unifiedProduct) {
            $directReceived = $this->stockInItems->where('product_id', $unifiedProduct->id)->where('status', 'received')->sum('quantity');
            $directRejected = $this->stockInItems->where('product_id', $unifiedProduct->id)->where('status', 'rejected')->sum('quantity');
            $directReplaced = $this->stockInItems->where('product_id', $unifiedProduct->id)->where('status', 'replaced')->sum('quantity');

            if ($directReceived > 0 || $directRejected > 0 || $directReplaced > 0) {
                $this->update([
                    'quantity_received' => $directReceived,
                    'quantity_rejected' => $directRejected,
                    'quantity_replaced' => $directReplaced,
                ]);
                return;
            }
        }

        $receivedCounts = [];
        $rejectedCounts = [];
        $replacedCounts = [];

        foreach ($this->kit->components as $component) {
            $receivedCounts[$component->id] = 0;
            $rejectedCounts[$component->id] = 0;
            $replacedCounts[$component->id] = 0;
        }

        foreach ($this->stockInItems as $stockItem) {
            $prodId = $stockItem->product_id;
            if (!isset($receivedCounts[$prodId])) {
                continue;
            }

            if ($stockItem->status === 'received') {
                $receivedCounts[$prodId] += $stockItem->quantity;
            } elseif ($stockItem->status === 'rejected') {
                $rejectedCounts[$prodId] += $stockItem->quantity;
            } elseif ($stockItem->status === 'replaced') {
                $replacedCounts[$prodId] += $stockItem->quantity;
            }
        }

        $minReceived = null;
        $minRejected = null;
        $minReplaced = null;

        foreach ($this->kit->components as $component) {
            $required = $component->pivot->quantity_required;
            if ($required <= 0) {
                continue;
            }

            $receivedKits = (int) floor(($receivedCounts[$component->id] ?? 0) / $required);
            $rejectedKits = (int) floor(($rejectedCounts[$component->id] ?? 0) / $required);
            $replacedKits = (int) floor(($replacedCounts[$component->id] ?? 0) / $required);

            if ($minReceived === null || $receivedKits < $minReceived) {
                $minReceived = $receivedKits;
            }
            if ($minRejected === null || $rejectedKits < $minRejected) {
                $minRejected = $rejectedKits;
            }
            if ($minReplaced === null || $replacedKits < $minReplaced) {
                $minReplaced = $replacedKits;
            }
        }

        $this->update([
            'quantity_received' => $minReceived ?? 0,
            'quantity_rejected' => $minRejected ?? 0,
            'quantity_replaced' => $minReplaced ?? 0,
        ]);
    }
}
