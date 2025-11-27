<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// 🔑 IMPORTANTE: Apuntamos a la nueva clase InventoryRequest
use App\Models\InventoryRequest; 
use App\Models\Product;
use App\Models\Kit;

class RequestItem extends Model
{
    use HasFactory;

    protected $table = 'request_items';

    protected $fillable = [
        'request_id',
        'product_id',
        'kit_id',
        'item_type',
        'quantity_requested',
        'unit_price_at_request',
    ];

    // Relación con la cabecera de la solicitud
    public function request(): BelongsTo
    {
        // 🔑 CLAVE: Usamos InventoryRequest::class
        return $this->belongsTo(InventoryRequest::class, 'request_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class, 'kit_id');
    }
}