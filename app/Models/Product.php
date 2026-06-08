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
        'is_generic',
        'created_on_the_fly',
        'user_id',
        'type',
        'requires_serial',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_kit' => 'boolean',
        'is_generic' => 'boolean',
        'created_on_the_fly' => 'boolean',
        'requires_serial' => 'boolean',
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

    // Componentes de un kit compuesto
    public function components()
    {
        return $this->belongsToMany(Product::class, 'product_kit_items', 'parent_id', 'child_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // Kits que contienen este producto
    public function parentKits()
    {
        return $this->belongsToMany(Product::class, 'product_kit_items', 'child_id', 'parent_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Obtiene el stock disponible virtual para este kit basado en el stock de sus componentes.
     */
    public function getAvailableStockAttribute(): int
    {
        if ($this->type !== 'composite_kit') {
            return $this->stock ?? 0;
        }

        if ($this->components->isEmpty()) {
            return 0;
        }

        $minKits = null;

        foreach ($this->components as $component) {
            $required = $component->pivot->quantity;
            if ($required <= 0) {
                continue;
            }
            $stock = $component->stock ?? 0;
            $formable = (int) floor($stock / $required);

            if ($minKits === null || $formable < $minKits) {
                $minKits = $formable;
            }
        }

        return $minKits ?? 0;
    }

    public static function getExpiringProducts(int $days = 30)
    {
        return self::where('track_expiry', true)
            ->whereHas('batches', function ($query) use ($days) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiration_date', '<=', now()->addDays($days))
                    ->whereDate('expiration_date', '>=', now());
            })
            ->with(['batches' => function ($query) use ($days) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiration_date', '<=', now()->addDays($days))
                    ->whereDate('expiration_date', '>=', now())
                    ->orderBy('expiration_date', 'asc');
            }])
            ->get();
    }

    public static function getExpiredProducts()
    {
        return self::where('track_expiry', true)
            ->whereHas('batches', function ($query) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiration_date', '<', now());
            })
            ->with(['batches' => function ($query) {
                $query->where('quantity', '>', 0)
                    ->whereDate('expiration_date', '<', now())
                    ->orderBy('expiration_date', 'asc');
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

    public function rfqItems()
    {
        return $this->hasMany(RfqItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockInItems()
    {
        return $this->hasMany(StockInItem::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class);
    }

    public function hasTransactionalHistory(): bool
    {
        return $this->rfqItems()->exists() ||
               $this->purchaseOrderItems()->exists() ||
               $this->stockInItems()->exists() ||
               $this->stockIns()->exists() ||
               $this->requestItems()->exists();
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
