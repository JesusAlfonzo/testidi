<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseQuote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'user_id',
        'code',
        'supplier_reference',
        'supplier_name_temp',
        'supplier_email_temp',
        'supplier_phone_temp',
        'date_issued',
        'valid_until',
        'delivery_date',
        'currency',
        'exchange_rate',
        'subtotal',
        'tax_amount',
        'total',
        'attachment_path',
        'status',
        'notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'valid_until' => 'date',
        'delivery_date' => 'date',
        'exchange_rate' => 'decimal:4',
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseQuoteItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'rfq_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function hasRegisteredSupplier(): bool
    {
        return !is_null($this->supplier_id);
    }

    public function getSupplierDisplayName(): string
    {
        if ($this->supplier_id && $this->supplier) {
            return $this->supplier->name;
        }
        return $this->supplier_name_temp ?? 'Proveedor no especificado';
    }

    public function getSupplierDisplayEmail(): string
    {
        if ($this->supplier_id && $this->supplier) {
            return $this->supplier->email ?? '-';
        }
        return $this->supplier_email_temp ?? '-';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning">Pendiente</span>',
            'selected' => '<span class="badge bg-info">Seleccionada</span>',
            'approved' => '<span class="badge bg-success">Aprobada</span>',
            'rejected' => '<span class="badge bg-danger">Rechazada</span>',
            'converted' => '<span class="badge bg-primary">Convertida a OC</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['pending']);
    }

    public function canBeSelected(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'selected';
    }

    public function canBeConverted(): bool
    {
        return $this->status === 'approved';
    }

    public static function generateCode(): string
    {
        $lastQuote = self::withTrashed()->latest('id')->first();
        $number = $lastQuote ? $lastQuote->id + 1 : 1;
        return 'COT-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
