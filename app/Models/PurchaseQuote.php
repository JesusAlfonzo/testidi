<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseQuote extends Model
{
    use HasFactory, SoftDeletes;

    // 🛡️ Campos que permitimos llenar masivamente
    protected $fillable = [
        'supplier_id',
        'user_id',
        'code',
        'supplier_reference',
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
        'notes'
    ];

    // 📅 Casting de fechas: Laravel las convertirá automáticamente en objetos Carbon
    protected $casts = [
        'date_issued' => 'date',
        'valid_until' => 'date',
        'delivery_date' => 'date',
        'exchange_rate' => 'decimal:4',
        'total' => 'decimal:2',
    ];

    // 🔗 RELACIONES

    // Una cotización tiene MUCHOS items
    public function items()
    {
        return $this->hasMany(PurchaseQuoteItem::class);
    }

    // Una cotización pertenece a UN proveedor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Una cotización fue creada por UN usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}