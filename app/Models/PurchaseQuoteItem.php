<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseQuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_quote_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_cost',
        'total_cost'
    ];

    // 🔗 RELACIONES

    // Un item pertenece a UNA cotización
    public function quote()
    {
        return $this->belongsTo(PurchaseQuote::class, 'purchase_quote_id');
    }

    // Un item hace referencia a UN producto del inventario
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}