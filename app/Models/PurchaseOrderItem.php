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
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'quantity_received',
        'quantity_rejected',
        'quantity_replaced',
        'unit_cost',
        'total_cost',
        'equivalent_bs',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'equivalent_bs' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
}
