<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'product_id',
        'cantidad',
        'precio_venta',
        'precio_compra',
        'subtotal',
        'ganancia',
    ];

    protected $casts = [
        'precio_venta'  => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'ganancia'      => 'decimal:2',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Factory method ───────────────────────────────────────────

    /**
     * Crea una instancia calculando automáticamente subtotal y ganancia.
     */
    public static function fromProduct(Product $product, int $cantidad): static
    {
        return new static([
            'product_id'    => $product->id,
            'cantidad'      => $cantidad,
            'precio_venta'  => $product->precio_venta,
            'precio_compra' => $product->precio_compra,
            'subtotal'      => $product->precio_venta * $cantidad,
            'ganancia'      => ($product->precio_venta - $product->precio_compra) * $cantidad,
        ]);
    }
}
