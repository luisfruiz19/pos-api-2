<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Obtén las categorías creadas por CategorySeeder
        $bebidas = Category::where('nombre', 'Bebidas')->first();
        $snacks = Category::where('nombre', 'Snacks')->first();
        $comidas = Category::where('nombre', 'Comidas')->first();
        $lacteos = Category::where('nombre', 'Lácteos')->first();

        // ── Catálogo fijo del kiosco ──────────────────────────────
        $productos = [
            // Bebidas
            ['nombre' => 'Agua San Luis 500ml',     'category_id' => $bebidas?->id, 'precio_compra' => 0.50, 'precio_venta' => 1.00, 'stock' => 80,  'stock_minimo' => 10, 'codigo_barras' => '7751030000001'],
            ['nombre' => 'Inca Kola 500ml',          'category_id' => $bebidas?->id, 'precio_compra' => 1.00, 'precio_venta' => 2.00, 'stock' => 60,  'stock_minimo' => 10, 'codigo_barras' => '7751030000002'],
            ['nombre' => 'Coca Cola 500ml',          'category_id' => $bebidas?->id, 'precio_compra' => 1.00, 'precio_venta' => 2.00, 'stock' => 60,  'stock_minimo' => 10, 'codigo_barras' => '7751030000003'],
            ['nombre' => 'Jugo Pulp Naranja 300ml',  'category_id' => $bebidas?->id, 'precio_compra' => 0.80, 'precio_venta' => 1.50, 'stock' => 40,  'stock_minimo' => 8,  'codigo_barras' => '7751030000004'],
            ['nombre' => 'Yogurt Gloria Fresa 150g', 'category_id' => $lacteos?->id, 'precio_compra' => 0.90, 'precio_venta' => 1.50, 'stock' => 30,  'stock_minimo' => 5,  'codigo_barras' => '7751030000005'],

            // Snacks
            ['nombre' => 'Papas Lays Classic',       'category_id' => $snacks?->id, 'precio_compra' => 0.70, 'precio_venta' => 1.50, 'stock' => 50,  'stock_minimo' => 8,  'codigo_barras' => '7751030000006'],
            ['nombre' => 'Galletas Oreo x3',         'category_id' => $snacks?->id, 'precio_compra' => 0.80, 'precio_venta' => 1.50, 'stock' => 45,  'stock_minimo' => 8,  'codigo_barras' => '7751030000007'],
            ['nombre' => 'Turrón D\'Onofrio',        'category_id' => $snacks?->id, 'precio_compra' => 0.60, 'precio_venta' => 1.00, 'stock' => 70,  'stock_minimo' => 10, 'codigo_barras' => '7751030000008'],
            ['nombre' => 'Canchita con queso',       'category_id' => $snacks?->id, 'precio_compra' => 0.50, 'precio_venta' => 1.00, 'stock' => 4,   'stock_minimo' => 5,  'codigo_barras' => null],  // stock bajo
            ['nombre' => 'Chizitos',                 'category_id' => $snacks?->id, 'precio_compra' => 0.50, 'precio_venta' => 1.00, 'stock' => 55,  'stock_minimo' => 8,  'codigo_barras' => '7751030000010'],

            // Preparados
            ['nombre' => 'Sándwich de pollo',        'category_id' => $comidas?->id, 'precio_compra' => 2.00, 'precio_venta' => 4.00, 'stock' => 20,  'stock_minimo' => 5,  'codigo_barras' => null],
            ['nombre' => 'Hot dog simple',            'category_id' => $comidas?->id, 'precio_compra' => 1.80, 'precio_venta' => 3.50, 'stock' => 15,  'stock_minimo' => 5,  'codigo_barras' => null],
            ['nombre' => 'Empanada de carne',        'category_id' => $comidas?->id, 'precio_compra' => 1.20, 'precio_venta' => 2.50, 'stock' => 25,  'stock_minimo' => 5,  'codigo_barras' => null],
            ['nombre' => 'Choclo con queso',         'category_id' => $comidas?->id, 'precio_compra' => 1.50, 'precio_venta' => 3.00, 'stock' => 3,   'stock_minimo' => 5,  'codigo_barras' => null],  // stock bajo
            ['nombre' => 'Arroz con leche',          'category_id' => $comidas?->id, 'precio_compra' => 0.80, 'precio_venta' => 1.50, 'stock' => 0,   'stock_minimo' => 5,  'codigo_barras' => null],  // agotado
        ];

        foreach ($productos as $data) {
            Product::create(array_merge($data, ['activo' => true]));
        }

        // Algunos productos aleatorios extra
        Product::factory()->count(5)->create();

        $this->command->info('✅ ProductSeeder: ' . (count($productos) + 5) . ' productos creados (incluye stock bajo y agotado).');
    }
}
