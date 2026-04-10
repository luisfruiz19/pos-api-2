<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CreditSale extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'total',
        'total_pagado',
        'saldo_pendiente',
        'estado',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'total_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    // ──── Relaciones ────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditSaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CreditPayment::class);
    }

    // ──── Scopes ────────────────────────

    public function scopeAbiertas(Builder $query): Builder
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeParciales(Builder $query): Builder
    {
        return $query->where('estado', 'parcial');
    }

    public function scopePagadas(Builder $query): Builder
    {
        return $query->where('estado', 'pagada');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereIn('estado', ['abierta', 'parcial']);
    }

    // ──── Helpers ────────────────────────

    public function pagarAbono(float $monto): void
    {
        $nuevoSaldoPendiente = $this->saldo_pendiente - $monto;

        $this->update([
            'total_pagado' => $this->total_pagado + $monto,
            'saldo_pendiente' => max(0, $nuevoSaldoPendiente),
            'estado' => $nuevoSaldoPendiente <= 0 ? 'pagada' : 'parcial',
        ]);

        // Actualizar deuda del cliente
        $this->customer->update([
            'saldo_deuda' => $this->customer->saldo_deuda - $monto,
            'ultima_pago_at' => now(),
        ]);
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'pagada';
    }

    public function tieneSaldoPendiente(): bool
    {
        return $this->saldo_pendiente > 0;
    }
}
