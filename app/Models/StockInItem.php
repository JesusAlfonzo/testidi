<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_id',
        'purchase_order_item_id',
        'product_id',
        'uom_id',
        'quantity',
        'quantity_received_uom',
        'quantity_received_base',
        'unit_cost',
        'batch_number',
        'expiration_date',
        'serial_number',
        'warehouse_location',
        'notes',
        'status',
        'rejection_reason',
        'replaced_item_id',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function replacedItem(): BelongsTo
    {
        return $this->belongsTo(StockInItem::class, 'replaced_item_id');
    }

    public function replacements()
    {
        return $this->hasMany(StockInItem::class, 'replaced_item_id');
    }
}
