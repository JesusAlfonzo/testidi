<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_product_id',
        'child_product_id',
        'conversion_factor',
    ];

    /**
     * Relación con el producto padre (caja/empaque mayor).
     */
    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    /**
     * Relación con el producto hijo (unidad individual).
     */
    public function childProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'child_product_id');
    }
}
