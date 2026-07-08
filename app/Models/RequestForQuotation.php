<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\GeneratesSequenceCode;

class RequestForQuotation extends Model
{
    use HasFactory, SoftDeletes, GeneratesSequenceCode;

    protected $sequenceYearly = true;

    protected $table = 'request_for_quotations';

    protected $fillable = [
        'code',
        'title',
        'description',
        'date_required',
        'delivery_deadline',
        'status',
        'notes',
        'created_by',
        'priority',
    ];

    protected $casts = [
        'date_required' => 'date',
        'delivery_deadline' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'rfq_id');
    }

    public function supplierOffers(): HasMany
    {
        return $this->hasMany(RfqSupplierOffer::class, 'rfq_id');
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class, 'rfq_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSequencePrefix(): string
    {
        return 'SDC';
    }

    public static function generateCode(): string
    {
        $sequenceService = app(\App\Services\SequenceService::class);
        $key = 'sdc:' . date('Y');
        $number = $sequenceService->getNextValue($key);
        return 'SDC-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function canConvertToPO(): bool
    {
        return in_array($this->status, ['sent', 'closed']) && !$this->purchaseOrder;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge bg-secondary">Borrador</span>',
            'sent' => '<span class="badge bg-primary">Enviada</span>',
            'closed' => '<span class="badge bg-success">Cerrada</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelada</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }
}
