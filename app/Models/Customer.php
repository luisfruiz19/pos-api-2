<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'saldo_deuda',
        'estado',
        'ultima_compra_at',
        'ultima_pago_at',
    ];

    protected $casts = [
        'saldo_deuda' => 'decimal:2',
        'ultima_compra_at' => 'datetime:Y-m-d\TH:i:s.u',
        'ultima_pago_at' => 'datetime:Y-m-d\TH:i:s.u',
    ];

    // ──── Relaciones ────────────────────────

    public function creditSales(): HasMany
    {
        return $this->hasMany(CreditSale::class);
    }

    // ──── Scopes ────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConDeuda(Builder $query): Builder
    {
        return $query->where('saldo_deuda', '>', 0);
    }

    public function scopeMorosos(Builder $query): Builder
    {
        return $query->where('estado', 'activo')
                     ->where('saldo_deuda', '>', 0)
                     ->where('ultima_pago_at', '<', now()->subDays(30));
    }

    // ──── Helpers ────────────────────────

    public function puedeComprarACredito(float $monto): bool
    {
        return $this->estado === 'activo';
    }
}
