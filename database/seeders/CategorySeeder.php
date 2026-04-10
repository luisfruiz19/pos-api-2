<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Bebidas', 'descripcion' => 'Bebidas frías y calientes', 'activo' => true],
            ['nombre' => 'Snacks', 'descripcion' => 'Aperitivos y meriendas', 'activo' => true],
            ['nombre' => 'Comidas', 'descripcion' => 'Platos preparados', 'activo' => true],
            ['nombre' => 'Dulces', 'descripcion' => 'Postres y dulces', 'activo' => true],
            ['nombre' => 'Lácteos', 'descripcion' => 'Productos lácteos', 'activo' => true],
            ['nombre' => 'Panadería', 'descripcion' => 'Pan y productos de panadería', 'activo' => true],
            ['nombre' => 'Frutas', 'descripcion' => 'Frutas frescas', 'activo' => true],
            ['nombre' => 'Verduras', 'descripcion' => 'Verduras frescas', 'activo' => true],
        ];

        foreach ($categorias as $categoria) {
            Category::create($categoria);
        }

        $this->command->info('✅ CategorySeeder: ' . count($categorias) . ' categorías creadas.');
    }
}
