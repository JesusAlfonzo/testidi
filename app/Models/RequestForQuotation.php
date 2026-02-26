<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestForQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'description',
        'date_required',
        'delivery_deadline',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_required' => 'date',
        'delivery_deadline' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'rfq_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(PurchaseQuote::class, 'rfq_id');
    }

    public static function generateCode(): string
    {
        $lastRfq = self::withTrashed()->latest('id')->first();
        $number = $lastRfq ? $lastRfq->id + 1 : 1;
        return 'RFQ-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
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
