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
        'product_id',
        'quantity',
        'unit_cost',
        'batch_number',
        'expiry_date',
        'serial_number',
        'warehouse_location',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
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
}
