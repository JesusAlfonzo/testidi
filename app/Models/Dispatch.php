<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\GeneratesSequenceCode;

class Dispatch extends Model
{
    use HasFactory, GeneratesSequenceCode;

    protected $sequenceField = 'dispatch_number';
    protected $sequenceYearly = true;
    protected $sequencePadding = 6;

    public function getSequencePrefix(): string
    {
        return 'DES';
    }

    protected $fillable = [
        'inventory_request_id',
        'dispatcher_id',
        'dispatch_number',
        'notes',
    ];

    /**
     * Relación con la solicitud original.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(InventoryRequest::class, 'inventory_request_id');
    }

    /**
     * Relación con el usuario que despachó.
     */
    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    /**
     * Relación con los ítems despachados.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DispatchItem::class, 'dispatch_id');
    }
}
