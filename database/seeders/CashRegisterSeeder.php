<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashRegisterSeeder extends Seeder
{
    public function run(): void
    {
        $cajeros = User::where('role', 'cajero')->get();

        // ── Caja abierta para cajero1 (sesión activa de hoy) ──────
        $cajero1 = User::where('username', 'cajero1')->first();
        if ($cajero1) {
            CashRegister::create([
                'user_id'        => $cajero1->id,
                'monto_apertura' => 100.00,
                'total_ventas'   => 0.00,
                'monto_esperado' => 100.00,
                'dinero_contado' => null,
                'diferencia'     => null,
                'estado'         => 'abierta',
                'observacion'    => null,
                'fecha_apertura' => now()->subHours(2),
                'fecha_cierre'   => null,
            ]);
        }

        // ── Cajas cerradas históricas (últimos 7 días) ────────────
        foreach ($cajeros->take(2) as $cajero) {
            for ($i = 1; $i <= 5; $i++) {
                $apertura     = now()->subDays($i)->setTime(7, 30);
                $totalVentas  = fake()->randomFloat(2, 80, 600);
                $montoApertura = 100.00;
                $montoEsperado = $montoApertura + $totalVentas;
                $dineroContado = $montoEsperado + fake()->randomFloat(2, -10, 10);

                CashRegister::create([
                    'user_id'        => $cajero->id,
                    'monto_apertura' => $montoApertura,
                    'total_ventas'   => $totalVentas,
                    'monto_esperado' => $montoEsperado,
                    'dinero_contado' => $dineroContado,
                    'diferencia'     => $dineroContado - $montoEsperado,
                    'estado'         => 'cerrada',
                    'observacion'    => fake()->optional(0.3)->sentence(),
                    'fecha_apertura' => $apertura,
                    'fecha_cierre'   => $apertura->copy()->addHours(fake()->numberBetween(4, 8)),
                ]);
            }
        }

        $this->command->info('✅ CashRegisterSeeder: cajas creadas (1 abierta + históricas).');
    }
}
