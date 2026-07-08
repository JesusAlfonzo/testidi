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
use App\Traits\GeneratesSequenceCode;

class InventoryRequest extends Model
{
    use HasFactory, LogsActivity, GeneratesSequenceCode;

    protected $sequenceYearly = true;

    protected $table = 'requests';

    // Constantes de Estado
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_PROCESSED = 'Processed';
    public const STATUS_PARTIALLY_PROCESSED = 'Procesado parcialmente';
    public const STATUS_DRAFT = 'Draft';

    // 🔑 CORRECCIÓN: Agregamos 'reference' para permitir su guardado
    protected $fillable = [
        'code',
        'requester_id', 
        'approver_id', 
        'status', 
        'justification',
        'rejection_reason', 
        'requested_at', 
        'processed_at', 
        'destination_area',
        'reference'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function getSequencePrefix(): string
    {
        return 'SDI';
    }

    // ---------------------- ACCESORES ----------------------

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['status'] ?? '') {
                    self::STATUS_PENDING => 'Pendiente',
                    self::STATUS_APPROVED => 'Aprobada',
                    self::STATUS_REJECTED => 'Rechazada',
                    self::STATUS_PROCESSED => 'Procesada',
                    self::STATUS_PARTIALLY_PROCESSED, 'Partially Processed' => 'Procesado parcialmente',
                    self::STATUS_DRAFT => 'Borrador',
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
                    self::STATUS_PENDING => 'warning',
                    self::STATUS_APPROVED => 'success',
                    self::STATUS_PROCESSED => 'success',
                    self::STATUS_REJECTED => 'danger',
                    self::STATUS_PARTIALLY_PROCESSED, 'Partially Processed' => 'info',
                    self::STATUS_DRAFT => 'secondary',
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

    /**
     * Relación con los despachos de la solicitud.
     */
    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class, 'inventory_request_id');
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