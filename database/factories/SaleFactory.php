<?php

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $total    = fake()->randomFloat(2, 1, 50);
        $ganancia = $total * fake()->randomFloat(2, 0.2, 0.5);

        return [
            'user_id'          => User::factory()->cajero(),
            'cash_register_id' => CashRegister::factory()->abierta(),
            'total'            => $total,
            'ganancia_total'   => $ganancia,
            'metodo_pago'      => fake()->randomElement(['efectivo', 'yape', 'plin']),
            'created_at'       => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function efectivo(): static
    {
        return $this->state(['metodo_pago' => 'efectivo']);
    }

    public function yape(): static
    {
        return $this->state(['metodo_pago' => 'yape']);
    }

    public function plin(): static
    {
        return $this->state(['metodo_pago' => 'plin']);
    }

    public function hoy(): static
    {
        return $this->state(['created_at' => now()]);
    }
}
