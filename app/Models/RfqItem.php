<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'item_type',
        'product_id',
        'kit_id',
        'quantity',
        'is_exempt',
        'notes',
    ];

    protected $casts = [
        'is_exempt' => 'boolean',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'rfq_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }
}
