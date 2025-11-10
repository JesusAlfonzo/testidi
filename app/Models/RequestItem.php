<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Apuntamos a la clase Request
use App\Models\Request;

class RequestItem extends Model
{
    use HasFactory;

    // Recomendado: Indica explícitamente el nombre de la tabla
    protected $table = 'request_items';

    protected $fillable = [
        'request_id',
        'product_id',
        'quantity_requested',
        'unit_price_at_request',
    ];

    // Relación con la cabecera de la solicitud
    public function request(): BelongsTo
    {
        // Apuntamos a la clase Request y usamos la clave foránea correcta
        return $this->belongsTo(Request::class, 'request_id');
    }

    // Relación con el producto solicitado
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
