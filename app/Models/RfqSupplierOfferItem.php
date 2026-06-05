<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqSupplierOfferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_supplier_offer_id',
        'product_id',
        'unit_price',
        'currency',
        'tax_status',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(RfqSupplierOffer::class, 'rfq_supplier_offer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
