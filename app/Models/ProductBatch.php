<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_in_item_id',
        'batch_number',
        'expiry_date',
        'serial_number',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockInItem(): BelongsTo
    {
        return $this->belongsTo(StockInItem::class, 'stock_in_item_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->between(now(), now()->addDays($days));
    }

    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    public static function consumeFromOldestBatch(int $productId, int $quantityRequired): array
    {
        $batches = self::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $consumed = [];
        $remaining = $quantityRequired;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $takeFromBatch = min($batch->quantity, $remaining);
            $batch->quantity -= $takeFromBatch;
            $batch->save();

            $consumed[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $takeFromBatch,
                'expiry_date' => $batch->expiry_date,
            ];

            $remaining -= $takeFromBatch;
        }

        if ($remaining > 0) {
            throw new \Exception("Stock insuficiente en lotes. Faltan {$remaining} unidades.");
        }

        return $consumed;
    }
}
