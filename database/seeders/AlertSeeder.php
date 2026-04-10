<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        // ── Alertas por productos agotados ────────────────────────
        Product::agotado()->each(function (Product $product) use (&$count) {
            Alert::create([
                'tipo'       => 'stock_agotado',
                'mensaje'    => "AGOTADO: {$product->nombre}",
                'detalle'    => "El producto '{$product->nombre}' se ha quedado sin stock. Reponer urgente.",
                'nivel'      => 'critical',
                'product_id' => $product->id,
                'leido'      => false,
                'created_at' => now()->subMinutes(fake()->numberBetween(5, 60)),
            ]);
            $count++;
        });

        // ── Alertas por stock bajo ────────────────────────────────
        Product::stockBajo()->each(function (Product $product) use (&$count) {
            Alert::create([
                'tipo'       => 'stock_bajo',
                'mensaje'    => "Stock bajo: {$product->nombre}",
                'detalle'    => "Quedan {$product->stock} unidades (mínimo configurado: {$product->stock_minimo}).",
                'nivel'      => 'warning',
                'product_id' => $product->id,
                'leido'      => false,
                'created_at' => now()->subMinutes(fake()->numberBetween(10, 120)),
            ]);
            $count++;
        });

        // ── Alertas informativas ya leídas (historial) ────────────
        $products = Product::activo()->limit(5)->get();
        foreach ($products as $product) {
            Alert::create([
                'tipo'       => 'stock_repuesto',
                'mensaje'    => "Stock repuesto: {$product->nombre}",
                'detalle'    => "Se registró una entrada de stock para '{$product->nombre}'.",
                'nivel'      => 'info',
                'product_id' => $product->id,
                'leido'      => true,
                'created_at' => now()->subDays(fake()->numberBetween(1, 5)),
            ]);
            $count++;
        }

        // ── Alerta general del sistema ────────────────────────────
        Alert::create([
            'tipo'       => 'sistema',
            'mensaje'    => 'Sistema iniciado correctamente',
            'detalle'    => 'El sistema POS Mater Admirabilis fue iniciado. Datos de prueba cargados.',
            'nivel'      => 'info',
            'product_id' => null,
            'leido'      => true,
            'created_at' => now()->subHour(),
        ]);
        $count++;

        $this->command->info("✅ AlertSeeder: {$count} alertas generadas.");
    }
}
