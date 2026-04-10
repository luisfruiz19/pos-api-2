<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Alert extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'mensaje',
        'detalle',
        'nivel',
        'product_id',
        'leido',
        'created_at',
    ];

    protected $casts = [
        'leido'      => 'boolean',
        'created_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeNoLeidas(Builder $query): Builder
    {
        return $query->where('leido', false);
    }

    public function scopeCriticas(Builder $query): Builder
    {
        return $query->where('nivel', 'critical');
    }

    public function scopeWarnings(Builder $query): Builder
    {
        return $query->where('nivel', 'warning');
    }

    // ─── Helpers / Factory methods ────────────────────────────────

    public function marcarLeida(): void
    {
        $this->update(['leido' => true]);
    }

    /**
     * Genera alerta de stock bajo para un producto.
     */
    public static function generarStockBajo(Product $product): static
    {
        return static::create([
            'tipo'       => 'stock_bajo',
            'mensaje'    => "Stock bajo: {$product->nombre}",
            'detalle'    => "Quedan {$product->stock} unidades (mínimo: {$product->stock_minimo})",
            'nivel'      => 'warning',
            'product_id' => $product->id,
            'leido'      => false,
            'created_at' => now(),
        ]);
    }

    /**
     * Genera alerta de stock agotado para un producto.
     */
    public static function generarStockAgotado(Product $product): static
    {
        return static::create([
            'tipo'       => 'stock_agotado',
            'mensaje'    => "AGOTADO: {$product->nombre}",
            'detalle'    => "El producto se ha quedado sin stock.",
            'nivel'      => 'critical',
            'product_id' => $product->id,
            'leido'      => false,
            'created_at' => now(),
        ]);
    }
}
