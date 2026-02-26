<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'purchase_quote_id',
        'supplier_id',
        'date_issued',
        'delivery_date',
        'delivery_address',
        'currency',
        'exchange_rate',
        'subtotal',
        'tax_amount',
        'total',
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
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(PurchaseQuote::class, 'purchase_quote_id');
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
            'draft' => '<span class="badge bg-secondary">Borrador</span>',
            'issued' => '<span class="badge bg-primary">Emitida</span>',
            'completed' => '<span class="badge bg-success">Completada</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelada</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
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
            if ($item->quantity_received < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    public static function generateCode(): string
    {
        $lastOrder = self::withTrashed()->latest('id')->first();
        $number = $lastOrder ? $lastOrder->id + 1 : 1;
        return 'OC-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
