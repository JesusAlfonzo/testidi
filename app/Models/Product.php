<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'category_id',
        'unit_id',
        'location_id',
        'brand_id',
        'code',
        'name',
        'description',
        'cost',
        'price',
        'stock',
        'min_stock',
        'expiry_warning_days',
        'track_expiry',
        'is_active',
        'is_kit',
        'created_on_the_fly',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_kit' => 'boolean',
        'created_on_the_fly' => 'boolean',
    ];

    // Relaciones con los módulos maestros
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // Trazabilidad
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public static function getExpiringProducts(int $days = 30)
    {
        return self::where('track_expiry', true)
            ->whereHas('batches', function ($query) use ($days) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '<=', now()->addDays($days))
                    ->whereDate('expiry_date', '>=', now());
            })
            ->with(['batches' => function ($query) use ($days) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '<=', now()->addDays($days))
                    ->whereDate('expiry_date', '>=', now())
                    ->orderBy('expiry_date', 'asc');
            }])
            ->get();
    }

    public static function getExpiredProducts()
    {
        return self::where('track_expiry', true)
            ->whereHas('batches', function ($query) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '<', now());
            })
            ->with(['batches' => function ($query) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '<', now())
                    ->orderBy('expiry_date', 'asc');
            }])
            ->get();
    }

    public function hasActiveBatches(): bool
    {
        return $this->batches()->where('quantity', '>', 0)->exists();
    }

    public function shouldUseFifo(): bool
    {
        return $this->track_expiry && $this->hasActiveBatches();
    }

    public function consumeStock(int $quantity, ?string $reason = null): array
    {
        if ($this->shouldUseFifo()) {
            $consumed = ProductBatch::consumeFromOldestBatch($this->id, $quantity);
            $this->stock -= $quantity;
            $this->save();
            return $consumed;
        }

        $this->stock -= $quantity;
        $this->save();
        return [['type' => 'simple', 'quantity' => $quantity]];
    }

    // Configuración del Log de Actividad de Spatie
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
