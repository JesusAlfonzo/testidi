<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Asumiendo que este es tu modelo de la cabecera de la solicitud
use App\Models\InventoryRequest; 
// Importar los modelos de Producto y Kit
use App\Models\Product; 
use App\Models\Kit; 

class RequestItem extends Model
{
    use HasFactory;

    // Recomendado: Indica explícitamente el nombre de la tabla
    protected $table = 'request_items';

    protected $fillable = [
        'request_id',
        'product_id',
        'kit_id', // 🔑 Asegúrate de que este también esté en fillable
        'item_type', // 🔑 Asegúrate de que este también esté en fillable
        'quantity_requested',
        'unit_price_at_request',
    ];

    /**
     * Relación con la cabecera de la solicitud (Many-to-One).
     */
    public function request(): BelongsTo
    {
        // Usamos InventoryRequest, que es la clase real de la solicitud
        return $this->belongsTo(InventoryRequest::class, 'request_id');
    }

    /**
     * Relación con el producto solicitado (solo si item_type es 'product').
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * 🔑 NUEVO: Relación con el kit solicitado (solo si item_type es 'kit').
     */
    public function kit(): BelongsTo
    {
        // Se relaciona con el modelo Kit usando la clave foránea 'kit_id'
        return $this->belongsTo(Kit::class, 'kit_id');
    }
}