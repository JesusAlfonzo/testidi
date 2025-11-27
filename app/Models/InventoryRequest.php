<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User; // Asegurar importación de User

class InventoryRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'requests';

    // 🔑 CORRECCIÓN: Agregamos 'reference' para permitir su guardado
    protected $fillable = [
        'requester_id', 
        'approver_id', 
        'status', 
        'justification',
        'rejection_reason', 
        'requested_at', 
        'processed_at', 
        'destination_area',
        'reference' // <-- ESTE CAMPO FALTABA
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // ---------------------- ACCESORES ----------------------

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['status'] ?? '') {
                    'Pending' => 'Pendiente',
                    'Approved' => 'Aprobada',
                    'Rejected' => 'Rechazada',
                    default => $attributes['status'] ?? 'Desconocido',
                };
            }
        );
    }
    
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['status'] ?? '') {
                    'Pending' => 'warning',
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default => 'secondary',
                };
            }
        );
    }

    // ---------------------- RELACIONES ----------------------

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequestItem::class, 'request_id', 'id');
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}