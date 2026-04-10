<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditSaleItem extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'credit_sale_id',
        'product_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // ──── Relaciones ────────────────────────

    public function creditSale(): BelongsTo
    {
        return $this->belongsTo(CreditSale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ──── Factory method ────────────────────────

    /**
     * Crea un item calculando automáticamente subtotal desde el producto.
     */
    public static function fromProduct(Product $product, int $cantidad): static
    {
        return new static([
            'product_id' => $product->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $product->precio_venta,
            'subtotal' => $product->precio_venta * $cantidad,
        ]);
    }
}
