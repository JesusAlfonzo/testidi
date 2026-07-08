<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\GeneratesSequenceCode;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, GeneratesSequenceCode;

    protected $sequenceYearly = true;

    protected $fillable = [
        'code',
        'rfq_id',
        'supplier_id',
        'date_issued',
        'delivery_date',
        'delivery_address',
        'currency',
        'exchange_rate',
        'iva_exempt',
        'subtotal',
        'tax_amount',
        'total',
        'subtotal_bs',
        'tax_amount_bs',
        'total_bs',
        'status',
        'terms',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'delivery_date' => 'date',
        'exchange_rate' => 'decimal:4',
        'iva_exempt' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'subtotal_bs' => 'decimal:2',
        'tax_amount_bs' => 'decimal:2',
        'total_bs' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge badge-secondary">Borrador</span>',
            'issued' => $this->isPartiallyReceived() 
                ? '<span class="badge badge-warning">Parcialmente Recibida</span>' 
                : '<span class="badge badge-info">Emitida</span>',
            'completed' => '<span class="badge badge-success">Cerrada / Recibida</span>',
            'cancelled' => '<span class="badge badge-danger">Anulada</span>',
            default => '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function isPartiallyReceived(): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        foreach ($this->items as $item) {
            if ($item->quantity_received > 0 || $item->quantity_replaced > 0) {
                return true;
            }
        }

        return false;
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeIssued(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'issued';
    }

    public function isFullyReceived(): bool
    {
        foreach ($this->items as $item) {
            if (($item->quantity_received + $item->quantity_replaced) < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    public function getSequencePrefix(): string
    {
        return 'ODC';
    }

    public static function generateCode(): string
    {
        $sequenceService = app(\App\Services\SequenceService::class);
        $key = 'odc:' . date('Y');
        $number = $sequenceService->getNextValue($key);
        return 'ODC-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'Bs' => 'Bs',
            default => $this->currency,
        };
    }

    public function getIsForeignCurrencyAttribute(): bool
    {
        return in_array($this->currency, ['USD', 'EUR']);
    }
}
