<?php

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashRegisterFactory extends Factory
{
    protected $model = CashRegister::class;

    public function definition(): array
    {
        $montoApertura = fake()->randomFloat(2, 50, 500);
        $totalVentas   = fake()->randomFloat(2, 0, 1000);
        $montoEsperado = $montoApertura + $totalVentas;

        return [
            'user_id'        => User::factory()->cajero(),
            'monto_apertura' => $montoApertura,
            'total_ventas'   => $totalVentas,
            'monto_esperado' => $montoEsperado,
            'dinero_contado' => null,
            'diferencia'     => null,
            'estado'         => 'abierta',
            'observacion'    => null,
            'fecha_apertura' => fake()->dateTimeBetween('-8 hours', 'now'),
            'fecha_cierre'   => null,
        ];
    }

    public function abierta(): static
    {
        return $this->state([
            'estado'        => 'abierta',
            'fecha_cierre'  => null,
            'dinero_contado' => null,
            'diferencia'     => null,
        ]);
    }

    public function cerrada(): static
    {
        return $this->state(function (array $attrs) {
            $montoEsperado  = ($attrs['monto_apertura'] ?? 100) + ($attrs['total_ventas'] ?? 200);
            $dineroContado  = fake()->randomFloat(2, $montoEsperado - 20, $montoEsperado + 20);
            return [
                'estado'         => 'cerrada',
                'monto_esperado' => $montoEsperado,
                'dinero_contado' => $dineroContado,
                'diferencia'     => $dineroContado - $montoEsperado,
                'fecha_cierre'   => fake()->dateTimeBetween('-1 hour', 'now'),
            ];
        });
    }
}
