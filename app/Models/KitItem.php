<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class KitItem extends Pivot
{
    // Define la conexión a la tabla pivote
    protected $table = 'kit_items';

    // Propiedad para indicar que no es autoincremental si usas claves compuestas (aunque aquí sí lo es)
    public $incrementing = true;

    protected $fillable = [
        'kit_id',
        'product_id',
        'quantity_required',
    ];
}