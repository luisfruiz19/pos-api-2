<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CreditSale;
use App\Models\CreditSaleItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CreditSaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products  = Product::where('activo', true)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️  CreditSaleSeeder: No hay clientes o productos disponibles.');
            return;
        }

        $totalVentas = 0;

        foreach ($customers as $customer) {
            // Cada cliente tiene entre 2 y 5 ventas a crédito
            $numVentas = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $numVentas; $i++) {
                // Seleccionar 1-4 productos distintos por venta
                $itemsVenta = $products->random(fake()->numberBetween(1, 4));
                $totalVenta = 0;

                // Crear venta a crédito
                $venta = CreditSale::create([
                    'customer_id' => $customer->id,
                    'total' => 0, // Se actualizará
                    'total_pagado' => 0,
                    'saldo_pendiente' => 0, // Se actualizará
                    'estado' => 'abierta',
                ]);

                // Crear items de la venta
                foreach ($itemsVenta as $product) {
                    if ($product->stock <= 0) continue;

                    $cantidad = fake()->numberBetween(1, min(5, $product->stock));
                    $subtotal = $product->precio_venta * $cantidad;
                    $totalVenta += $subtotal;

                    CreditSaleItem::create([
                        'credit_sale_id' => $venta->id,
                        'product_id' => $product->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $product->precio_venta,
                        'subtotal' => $subtotal,
                    ]);

                    // Decrementar stock
                    $product->decrement('stock', $cantidad);
                }

                // Actualizar totales de la venta
                $venta->update([
                    'total' => $totalVenta,
                    'saldo_pendiente' => $totalVenta,
                ]);

                // Actualizar deuda del cliente
                $customer->increment('saldo_deuda', $totalVenta);

                $totalVentas++;
            }
        }

        $this->command->info("✅ CreditSaleSeeder: {$totalVentas} ventas a crédito creadas con items.");
    }
}
