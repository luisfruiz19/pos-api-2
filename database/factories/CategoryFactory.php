<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private array $categorias = [
        ['nombre' => 'Bebidas', 'descripcion' => 'Bebidas frías y calientes'],
        ['nombre' => 'Snacks', 'descripcion' => 'Aperitivos y meriendas'],
        ['nombre' => 'Comidas', 'descripcion' => 'Platos preparados'],
        ['nombre' => 'Dulces', 'descripcion' => 'Postres y dulces'],
        ['nombre' => 'Lácteos', 'descripcion' => 'Productos lácteos'],
        ['nombre' => 'Panadería', 'descripcion' => 'Pan y productos de panadería'],
        ['nombre' => 'Frutas', 'descripcion' => 'Frutas frescas'],
        ['nombre' => 'Verduras', 'descripcion' => 'Verduras frescas'],
    ];

    public function definition(): array
    {
        $categoria = fake()->randomElement($this->categorias);

        return [
            'nombre' => $categoria['nombre'],
            'descripcion' => $categoria['descripcion'],
            'activo' => true,
        ];
    }

    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
