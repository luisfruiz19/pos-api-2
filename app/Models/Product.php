<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nombre',
        'category_id',
        'precio_compra',
        'precio_venta',
        'stock',
        'stock_minimo',
        'codigo_barras',
        'imagen',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta'  => 'decimal:2',
        'activo'        => 'boolean',
    ];

    public function getImagenAttribute(mixed $value): ?string
    {
        if (empty($value) || !is_string($value)) {
            return null;
        }

        $relative = Storage::disk('public')->url($value);

        // `Storage::url()` puede devolver relativo; `url()` lo convierte a absoluto.
        return url($relative);
    }

    // ─── Relaciones ───────────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0);
    }

    public function scopeAgotado(Builder $query): Builder
    {
        return $query->where('stock', 0);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function margenGanancia(): float
    {
        if ($this->precio_compra == 0) return 0;
        return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
    }

    public function tieneStockSuficiente(int $cantidad): bool
    {
        return $this->stock >= $cantidad;
    }

    public function estaAgotado(): bool
    {
        return $this->stock <= 0;
    }

    public function tieneStockBajo(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->stock_minimo;
    }
}
