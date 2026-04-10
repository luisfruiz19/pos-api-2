<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    // Productos típicos de un kiosco escolar
    private array $productosKiosco = [
        ['nombre' => 'Agua 500ml',          'compra' => 0.50, 'venta' => 1.00],
        ['nombre' => 'Gaseosa 500ml',        'compra' => 1.00, 'venta' => 2.00],
        ['nombre' => 'Galletas Oreo',        'compra' => 0.80, 'venta' => 1.50],
        ['nombre' => 'Choclo con queso',     'compra' => 1.50, 'venta' => 3.00],
        ['nombre' => 'Sándwich de pollo',    'compra' => 2.00, 'venta' => 4.00],
        ['nombre' => 'Jugo de fruta 250ml',  'compra' => 0.80, 'venta' => 1.50],
        ['nombre' => 'Yogurt Gloria 150g',   'compra' => 0.90, 'venta' => 1.50],
        ['nombre' => 'Papas Lays',           'compra' => 0.70, 'venta' => 1.50],
        ['nombre' => 'Canchita con queso',   'compra' => 0.50, 'venta' => 1.00],
        ['nombre' => 'Hot dog',              'compra' => 1.80, 'venta' => 3.50],
        ['nombre' => 'Empanada de carne',    'compra' => 1.20, 'venta' => 2.50],
        ['nombre' => 'Turrón D\'Onofrio',    'compra' => 0.60, 'venta' => 1.00],
    ];

    public function definition(): array
    {
        $producto = fake()->randomElement($this->productosKiosco);

        return [
            'nombre'        => $producto['nombre'],
            'category_id'   => Category::inRandomOrder()->first()?->id,
            'precio_compra' => $producto['compra'],
            'precio_venta'  => $producto['venta'],
            'stock'         => fake()->numberBetween(0, 100),
            'stock_minimo'  => fake()->numberBetween(2, 10),
            'codigo_barras' => fake()->optional(0.7)->ean13(),
            'imagen'        => null,
            'activo'        => true,
        ];
    }

    public function agotado(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function stockBajo(): static
    {
        return $this->state(function (array $attrs) {
            $minimo = $attrs['stock_minimo'] ?? 2;
            return ['stock' => fake()->numberBetween(1, $minimo)];
        });
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
