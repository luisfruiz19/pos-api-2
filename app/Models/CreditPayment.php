<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditPayment extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'credit_sale_id',
        'monto',
        'metodo_pago',
        'observacion',
        'created_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // ──── Relaciones ────────────────────────

    public function creditSale(): BelongsTo
    {
        return $this->belongsTo(CreditSale::class);
    }
}
