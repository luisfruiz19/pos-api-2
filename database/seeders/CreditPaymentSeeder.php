<?php

namespace Database\Seeders;

use App\Models\CreditSale;
use App\Models\CreditPayment;
use Illuminate\Database\Seeder;

class CreditPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $ventas = CreditSale::where('estado', '!=', 'incobrable')->get();
        $metodosPago = ['efectivo', 'yape', 'plin', 'transferencia'];

        if ($ventas->isEmpty()) {
            $this->command->warn('⚠️  CreditPaymentSeeder: No hay ventas a crédito para registrar pagos.');
            return;
        }

        $totalPagos = 0;

        foreach ($ventas as $venta) {
            // 60% de probabilidad de que tenga al menos un pago
            if (fake()->boolean(60)) {
                $saldoRestante = $venta->saldo_pendiente;
                $numPagos = fake()->numberBetween(1, 3);

                for ($i = 0; $i < $numPagos && $saldoRestante > 0; $i++) {
                    // Última cuota paga completo, antes son parciales
                    $esUltima = $i === $numPagos - 1;

                    if ($esUltima) {
                        $monto = $saldoRestante; // Pagar el rest
                    } else {
                        // Pagar entre 30% y 70% del saldo
                        $porcentaje = fake()->numberBetween(30, 70);
                        $monto = (int) ($saldoRestante * $porcentaje / 100);
                        $monto = min($monto, $saldoRestante); // No superar el saldo
                    }

                    if ($monto <= 0) continue;

                    CreditPayment::create([
                        'credit_sale_id' => $venta->id,
                        'monto' => $monto,
                        'metodo_pago' => fake()->randomElement($metodosPago),
                        'observacion' => fake()->optional(0.3)->sentence(4),
                        'created_at' => fake()->dateTimeBetween($venta->created_at, 'now'),
                    ]);

                    $saldoRestante -= $monto;
                    $totalPagos++;
                }

                // Actualizar totales de la venta
                $totalPagado = $venta->total - $saldoRestante;
                $nuevoEstado = $saldoRestante == 0 ? 'pagada' : ($totalPagado > 0 ? 'parcial' : 'abierta');

                $venta->update([
                    'total_pagado' => $totalPagado,
                    'saldo_pendiente' => max(0, $saldoRestante),
                    'estado' => $nuevoEstado,
                ]);

                // Actualizar deuda del cliente (restar los pagos)
                $venta->customer->decrement('saldo_deuda', $totalPagado);
            }
        }

        $this->command->info("✅ CreditPaymentSeeder: {$totalPagos} pagos registrados.");
    }
}
