<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $cajeros     = User::where('role', 'cajero')->get();
        $cajas       = CashRegister::where('estado', 'cerrada')->get();
        $products    = Product::where('activo', true)->where('stock', '>', 0)->get();
        $metodos     = ['efectivo', 'yape', 'plin'];

        if ($cajas->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️  SaleSeeder: No hay cajas cerradas o productos disponibles.');
            return;
        }

        $totalVentas = 0;

        foreach ($cajas as $caja) {
            $cajero         = $cajeros->random();
            $numVentas      = fake()->numberBetween(5, 20);

            for ($v = 0; $v < $numVentas; $v++) {
                // ── Seleccionar 1-4 productos distintos por venta ─
                $itemsVenta   = $products->random(fake()->numberBetween(1, 4));
                $totalVenta   = 0;
                $gananciaVenta = 0;
                $detalles     = [];

                foreach ($itemsVenta as $product) {
                    $cantidad     = fake()->numberBetween(1, 3);
                    $subtotal     = $product->precio_venta * $cantidad;
                    $ganancia     = ($product->precio_venta - $product->precio_compra) * $cantidad;

                    $totalVenta    += $subtotal;
                    $gananciaVenta += $ganancia;

                    $detalles[] = [
                        'id'            => Str::uuid(),
                        'product_id'    => $product->id,
                        'cantidad'      => $cantidad,
                        'precio_venta'  => $product->precio_venta,
                        'precio_compra' => $product->precio_compra,
                        'subtotal'      => $subtotal,
                        'ganancia'      => $ganancia,
                    ];
                }

                // ── Crear la venta ─────────────────────────────────
                $fechaVenta = fake()->dateTimeBetween(
                    $caja->fecha_apertura,
                    $caja->fecha_cierre ?? now()
                );

                $sale = Sale::create([
                    'user_id'          => $cajero->id,
                    'cash_register_id' => $caja->id,
                    'total'            => round($totalVenta, 2),
                    'ganancia_total'   => round($gananciaVenta, 2),
                    'metodo_pago'      => fake()->randomElement($metodos),
                    'created_at'       => $fechaVenta,
                ]);

                // ── Insertar detalles ──────────────────────────────
                foreach ($detalles as $detalle) {
                    SaleDetail::create(array_merge($detalle, ['sale_id' => $sale->id]));
                }

                // ── Registrar movimientos de inventario ───────────
                foreach ($detalles as $detalle) {
                    InventoryMovement::create([
                        'product_id' => $detalle['product_id'],
                        'tipo'       => 'salida',
                        'cantidad'   => $detalle['cantidad'],
                        'motivo'     => 'Venta #' . $sale->id,
                        'user_id'    => $cajero->id,
                        'created_at' => $fechaVenta,
                    ]);
                }

                // ── Acumular en caja ───────────────────────────────
                $caja->increment('total_ventas', round($totalVenta, 2));
                $totalVentas++;
            }
        }

        // ── Algunos movimientos de entrada (reposición) ───────────
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $products->random(5)->each(function (Product $p) use ($admin) {
                InventoryMovement::create([
                    'product_id' => $p->id,
                    'tipo'       => 'entrada',
                    'cantidad'   => fake()->numberBetween(10, 50),
                    'motivo'     => 'Reposición de stock',
                    'user_id'    => $admin->id,
                    'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
                ]);
            });
        }

        $this->command->info("✅ SaleSeeder: {$totalVentas} ventas creadas con detalles y movimientos de inventario.");
    }
}
