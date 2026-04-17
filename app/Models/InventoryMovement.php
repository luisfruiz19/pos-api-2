<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'tipo',
        'cantidad',
        'motivo',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s.u',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }

    public function scopeAjustes($query)
    {
        return $query->where('tipo', 'ajuste');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Crea un movimiento de salida por venta y descuenta el stock.
     */
    public static function registrarSalida(
        Product $product,
        int $cantidad,
        User $user,
        string $motivo = 'Venta'
    ): static {
        // Protección de stock negativo
        if ($product->stock < $cantidad) {
            throw new \DomainException("Stock insuficiente para el producto: {$product->nombre}");
        }

        $movement = static::create([
            'product_id' => $product->id,
            'tipo'       => 'salida',
            'cantidad'   => $cantidad,
            'motivo'     => $motivo,
            'user_id'    => $user->id,
            'created_at' => now(),
        ]);

        $product->decrement('stock', $cantidad);

        return $movement;
    }

    /**
     * Crea un movimiento de entrada y suma stock.
     */
    public static function registrarEntrada(
        Product $product,
        int $cantidad,
        User $user,
        string $motivo = 'Reposición de stock'
    ): static {
        $movement = static::create([
            'product_id' => $product->id,
            'tipo'       => 'entrada',
            'cantidad'   => $cantidad,
            'motivo'     => $motivo,
            'user_id'    => $user->id,
            'created_at' => now(),
        ]);

        $product->increment('stock', $cantidad);

        return $movement;
    }
}
