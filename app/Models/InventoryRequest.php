<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    // 🔑 Debe apuntar al nombre de la tabla (plurar y snake_case)
    protected $table = 'requests';

    // Los campos que pueden ser llenados masivamente
    protected $fillable = [
        'requester_id', 'approver_id', 'status', 'justification',
        'rejection_reason', 'requested_at', 'processed_at', 'destination_area'
    ];

    // Casting de fechas
    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // ---------------------- RELACIONES ----------------------

    // Relación con el usuario que creó la solicitud (requester_id)
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    // Relación con el usuario que aprobó la solicitud (approver_id)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Relación con los ítems de la solicitud (RequestItem)
    public function items()
    {
        // Debe apuntar al modelo que maneja los ítems de la solicitud
        return $this->hasMany(RequestItem::class, 'request_id');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        // 🔑 CLAVE: Forzamos a Laravel a usar 'id' como clave de la ruta.
        return 'id';
    }
}
