<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active', 'unit_price'];

    // Relación muchos a muchos con Product
    public function components()
    {
        return $this->belongsToMany(Product::class, 'kit_items')
                    ->withPivot('quantity_required')
                    ->using(KitItem::class); // Usar el modelo pivote
    }

    /**
     * Obtiene el stock disponible para este kit basado en el stock de sus componentes individuales.
     */
    public function getAvailableStockAttribute(): int
    {
        if ($this->components->isEmpty()) {
            return 0;
        }

        $minKits = null;

        foreach ($this->components as $component) {
            $required = $component->pivot->quantity_required;
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
}