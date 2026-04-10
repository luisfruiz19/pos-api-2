<?php

namespace Database\Factories;

use App\Models\CreditSale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditSale>
 */
class CreditSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(10000, 100000);
        $totalPagado = fake()->numberBetween(0, $total);

        return [
            'customer_id' => \App\Models\Customer::factory(),
            'total' => $total,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $total - $totalPagado,
            'estado' => $totalPagado == 0 ? 'abierta' : ($totalPagado >= $total ? 'pagada' : 'parcial'),
        ];
    }
}
