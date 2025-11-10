<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    // Relación con el usuario que la creó
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 🎯 Configuración del Log de Actividad de Spatie
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Registrar todos los cambios en $fillable
            ->logOnlyDirty() // Solo registrar los atributos que cambiaron
            ->dontSubmitEmptyLogs(); // No registrar si nada cambió
    }
}
