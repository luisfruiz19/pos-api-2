<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

use function Symfony\Component\Clock\now;

class CashRegister extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false; // Maneja sus propios campos de fecha

    protected $fillable = [
        'user_id',
        'monto_apertura',
        'total_ventas',
        'monto_esperado',
        'dinero_contado',
        'diferencia',
        'estado',
        'observacion',
        'fecha_apertura',
        'fecha_cierre',
    ];

    protected $casts = [
        'monto_apertura'  => 'decimal:2',
        'total_ventas'    => 'decimal:2',
        'monto_esperado'  => 'decimal:2',
        'dinero_contado'  => 'decimal:2',
        'diferencia'      => 'decimal:2',
        'fecha_apertura'  => 'datetime:Y-m-d\TH:i:s.u',
        'fecha_cierre'    => 'datetime:Y-m-d\TH:i:s.u',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeAbierta($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeCerrada($query)
    {
        return $query->where('estado', 'cerrada');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    /**
     * Cierra la caja: calcula diferencia y actualiza estado.
     */
    public function cerrar(float $dineroContado, ?string $observacion = null): void
    {
        $montoEsperado = $this->monto_apertura + $this->total_ventas;

        $this->update([
            'dinero_contado'  => $dineroContado,
            'monto_esperado'  => $montoEsperado,
            'diferencia'      => $dineroContado - $montoEsperado,
            'estado'          => 'cerrada',
            'observacion'     => $observacion,
            'fecha_cierre'    => now(),
        ]);
    }

    /**
     * Suma una venta al acumulado de la caja.
     */
    public function acumularVenta(float $total): void
    {
        $this->increment('total_ventas', $total);
        $this->update(['monto_esperado' => $this->monto_apertura + $this->total_ventas]);
    }
}
