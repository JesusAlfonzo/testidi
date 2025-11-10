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
}