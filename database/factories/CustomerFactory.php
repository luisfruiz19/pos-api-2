<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName() . ' ' . fake()->lastName(),
            'telefono' => fake()->numerify('9########'),
            'direccion' => fake()->address(),
            'saldo_deuda' => 0,
            'estado' => 'activo',
            'ultima_compra_at' => fake()->dateTimeBetween('-30 days'),
            'ultima_pago_at' => fake()->optional(0.3)->dateTimeBetween('-15 days'),
        ];
    }
}
