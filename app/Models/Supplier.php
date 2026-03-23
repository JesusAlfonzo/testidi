<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supplier extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'tax_id',
        'contact_person',
        'phone',
        'phones',
        'email',
        'address',
        'fiscal_address',
        'representative_cedula',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'phones' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Un proveedor tiene MUCHAS cotizaciones
    public function quotes()
    {
        return $this->hasMany(PurchaseQuote::class);
    }
}
