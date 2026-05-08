<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StockIn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'purchase_order_id',
        'document_type',
        'document_number',
        'invoice_number',
        'delivery_note_number',
        'quantity',
        'unit_cost',
        'reason',
        'type',
        'original_stock_in_id',
        'entry_date',
        'user_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    // Relaciones
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockInItem::class);
    }

    public function originalStockIn()
    {
        return $this->belongsTo(StockIn::class, 'original_stock_in_id');
    }

    public function replacements()
    {
        return $this->hasMany(StockIn::class, 'original_stock_in_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
