<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_id',
        'product_id',
        'batch_id',
        'quantity_requested',
        'quantity_dispatched',
        'status',
    ];

    /**
     * Relación con el despacho padre.
     */
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class, 'dispatch_id');
    }

    /**
     * Relación con el producto despachado.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relación con el lote de producto de origen (para trazabilidad).
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
