<?php

use App\Models\Customer;
use App\Models\CreditSale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->user, 'sanctum');
});

describe('End-to-End Credit Sales Workflow', function () {

    it('can complete full credit sales flow from customer creation to payment', function () {
        // Paso 1: Crear cliente
        $createCustomerResponse = $this->postJson('/api/customers', [
            'nombre' => 'Don Juan Pérez',
            'telefono' => '999111222',
            'direccion' => 'Av. Principal 123',
        ]);

        $createCustomerResponse->assertStatus(201);
        $customerId = $createCustomerResponse->json('data.id');

        expect($customerId)->not->toBeNull();

        // Paso 2: Verificar que cliente fue creado correctamente
        $getCustomerResponse = $this->getJson("/api/customers/{$customerId}");
        $getCustomerResponse->assertStatus(200);

        expect($getCustomerResponse->json('data.nombre'))->toBe('Don Juan Pérez');
        expect((float) $getCustomerResponse->json('data.saldo_deuda'))->toBe(0.0);

        // Paso 3: Crear productos
        $product1 = Product::factory()->create(['nombre' => 'Arroz', 'precio_venta' => 5000, 'stock' => 100]);
        $product2 = Product::factory()->create(['nombre' => 'Refresco', 'precio_venta' => 2500, 'stock' => 200]);

        $initialStock1 = $product1->stock;
        $initialStock2 = $product2->stock;

        // Paso 4: Crear venta a crédito
        $createSaleResponse = $this->postJson('/api/credit-sales', [
            'customer_id' => $customerId,
            'items' => [
                ['product_id' => $product1->id, 'cantidad' => 10],
                ['product_id' => $product2->id, 'cantidad' => 20],
            ],
        ]);

        $createSaleResponse->assertStatus(201);
        $creditSaleId = $createSaleResponse->json('data.id');

        $totalExpected = (10 * 5000) + (20 * 2500);
        expect((float) $createSaleResponse->json('data.total'))->toBe((float) $totalExpected);
        expect($createSaleResponse->json('data.estado'))->toBe('abierta');

        // Paso 5: Verificar stock fue decrementado
        $product1->refresh();
        $product2->refresh();

        expect($product1->stock)->toBe($initialStock1 - 10);
        expect($product2->stock)->toBe($initialStock2 - 20);

        // Paso 6: Verificar deuda del cliente fue actualizada
        $customerCheck = $this->getJson("/api/customers/{$customerId}");
        expect((float) $customerCheck->json('data.saldo_deuda'))->toBe((float) $totalExpected);

        // Paso 7: Hacer pago parcial
        $partialPaymentResponse = $this->postJson(
            "/api/credit-sales/{$creditSaleId}/payment",
            [
                'monto' => 50000,
                'metodo_pago' => 'efectivo',
                'observacion' => 'Primer abono',
            ]
        );

        $partialPaymentResponse->assertStatus(200);
        expect($partialPaymentResponse->json('data.estado'))->toBe('parcial');
        expect((float) $partialPaymentResponse->json('data.saldo_pendiente'))->toBe((float) ($totalExpected - 50000));

        // Paso 8: Verificar deuda se actualizó
        $customerAfterPayment = $this->getJson("/api/customers/{$customerId}");
        expect((float) $customerAfterPayment->json('data.saldo_deuda'))->toBe((float) ($totalExpected - 50000));

        // Paso 9: Hacer segundo pago
        $finalPaymentResponse = $this->postJson(
            "/api/credit-sales/{$creditSaleId}/payment",
            [
                'monto' => $totalExpected - 50000,
                'metodo_pago' => 'transferencia',
                'observacion' => 'Pago final',
            ]
        );

        $finalPaymentResponse->assertStatus(200);
        expect($finalPaymentResponse->json('data.estado'))->toBe('pagada');
        expect((float) $finalPaymentResponse->json('data.saldo_pendiente'))->toBe(0.0);

        // Paso 10: Verificar deuda es cero
        $customerFinal = $this->getJson("/api/customers/{$customerId}");
        expect((float) $customerFinal->json('data.saldo_deuda'))->toBe(0.0);

        // Paso 11: Verificar venta fue marcada como pagada
        $creditSaleCheck = $this->getJson("/api/credit-sales/{$creditSaleId}");
        expect($creditSaleCheck->json('data.estado'))->toBe('pagada');
        expect((float) $creditSaleCheck->json('data.total_pagado'))->toBe((float) $totalExpected);
    });

    it('allows customer to buy any amount when active', function () {
        $customer = Customer::factory()->create(['estado' => 'activo']);
        $product = Product::factory()->create(['precio_venta' => 10000, 'stock' => 1000]);

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 100], // Any amount is allowed
            ],
        ]);

        $response->assertStatus(201);
        expect((float) $response->json('data.total'))->toBe(1000000.0);
    });

    it('prevents sale when stock is insufficient', function () {
        $customer = Customer::factory()->create(['estado' => 'activo']);
        $product = Product::factory()->create(['precio_venta' => 5000, 'stock' => 5]);

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 10], // > 5 available
            ],
        ]);

        $response->assertStatus(422);
        expect($response->json('message'))->toContain('Stock insuficiente');
    });

    it('validates payment amount does not exceed pending balance', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['precio_venta' => 1000, 'stock' => 100]);

        $saleResponse = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 10],
            ],
        ]);

        $creditSaleId = $saleResponse->json('data.id');

        $paymentResponse = $this->postJson(
            "/api/credit-sales/{$creditSaleId}/payment",
            [
                'monto' => 50000, // Mucho más que el balance de 10,000
                'metodo_pago' => 'efectivo',
            ]
        );

        $paymentResponse->assertStatus(422);
        expect($paymentResponse->json('message'))->toContain('monto excede');
    });

    it('can list customers with debt', function () {
        Customer::factory(5)->create(['saldo_deuda' => 0]);
        Customer::factory(3)->create(['saldo_deuda' => 10000]);

        $response = $this->getJson('/api/customers?con_deuda=1');

        $response->assertStatus(200);
        expect($response->json('total'))->toBe(3);
    });

    it('shows customer credit summary correctly', function () {
        $customer = Customer::factory()->create([
            'saldo_deuda' => 30000,
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}/summary");

        $response->assertStatus(200);
        expect((float) $response->json('resumen.saldo_deuda'))->toBe(30000.0);
    });

    it('can view credit history for customer', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['precio_venta' => 5000, 'stock' => 1000]);

        // Create 3 credit sales
        foreach (range(1, 3) as $i) {
            $this->postJson('/api/credit-sales', [
                'customer_id' => $customer->id,
                'items' => [
                    ['product_id' => $product->id, 'cantidad' => $i],
                ],
            ]);
        }

        $response = $this->getJson("/api/customers/{$customer->id}/credit-history");

        $response->assertStatus(200);
        expect(count($response->json('data')))->toBe(3);
    });

    it('tracks payment history correctly', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['precio_venta' => 10000, 'stock' => 1000]);

        $saleResponse = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 5],
            ],
        ]);

        $creditSaleId = $saleResponse->json('data.id');

        // Make 3 payments
        $payments = [];
        foreach ([10000, 15000, 25000] as $monto) {
            $response = $this->postJson(
                "/api/credit-sales/{$creditSaleId}/payment",
                [
                    'monto' => $monto,
                    'metodo_pago' => 'efectivo',
                    'observacion' => "Pago de {$monto}",
                ]
            );
            $payments[] = $response->json('data');
        }

        // View payment history
        $paymentsResponse = $this->getJson("/api/credit-sales/{$creditSaleId}/payments");

        $paymentsResponse->assertStatus(200);
        expect(count($paymentsResponse->json('pagos')))->toBe(3);
        expect((float) $paymentsResponse->json('total_pagado'))->toBe(50000.0);
    });

    it('marks customer as inactive prevents credit purchases', function () {
        $customer = Customer::factory()->create(['estado' => 'inactivo']);
        $product = Product::factory()->create(['precio_venta' => 5000, 'stock' => 100]);

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 5],
            ],
        ]);

        $response->assertStatus(422);
        expect($response->json('message'))->toContain('inactivo');
    });

    it('can update customer information', function () {
        $customer = Customer::factory()->create();

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'nombre' => 'Don Juan Updated',
            'telefono' => '99999999',
        ]);

        $response->assertStatus(200);
        expect($response->json('data.nombre'))->toBe('Don Juan Updated');
        expect($response->json('data.telefono'))->toBe('99999999');
    });

    it('validates credit payment form request', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['precio_venta' => 5000, 'stock' => 100]);

        $saleResponse = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 5],
            ],
        ]);

        $creditSaleId = $saleResponse->json('data.id');

        // Test invalid payment method - should reject (422 or 400)
        $response = $this->postJson(
            "/api/credit-sales/{$creditSaleId}/payment",
            [
                'monto' => 25000,
                'metodo_pago' => 'bitcoin', // Invalid
            ]
        );

        expect($response->status())->toBeGreaterThanOrEqual(400);
        expect($response->status())->toBeLessThan(500);
    });

    it('accumulates debt correctly across multiple sales', function () {
        $customer = Customer::factory()->create(['saldo_deuda' => 0]);
        $product = Product::factory()->create(['precio_venta' => 10000, 'stock' => 500]);

        // First sale
        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 5],
            ],
        ]);

        $customer->refresh();
        expect((float) $customer->saldo_deuda)->toBe(50000.0);

        // Second sale
        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 3],
            ],
        ]);

        $customer->refresh();
        expect((float) $customer->saldo_deuda)->toBe(80000.0);
    });

    it('creates inventory movements for audit trail', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['precio_venta' => 5000, 'stock' => 100]);

        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 10],
            ],
        ]);

        $movementCount = DB::table('inventory_movements')
            ->where('product_id', $product->id)
            ->where('tipo', 'salida')
            ->count();

        expect($movementCount)->toBeGreaterThan(0);
    });

    it('handles multiple items in single credit sale', function () {
        $customer = Customer::factory()->create(['estado' => 'activo']);
        $products = Product::factory(5)->create(['precio_venta' => 1000, 'stock' => 100]);

        $items = [];
        foreach ($products as $index => $product) {
            $items[] = ['product_id' => $product->id, 'cantidad' => ($index + 1)];
        }

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => $items,
        ]);

        $response->assertStatus(201);
        $itemCount = count($response->json('data.items'));
        expect($itemCount)->toBeGreaterThanOrEqual(1); // At least 1 item created

        // 1 + 2 + 3 + 4 + 5 = 15 items * 1000 = 15,000
        expect((float) $response->json('data.total'))->toBe(15000.0);
    });

    it('respects database transactions atomicity', function () {
        $customer = Customer::factory()->create(['estado' => 'activo']);
        $product = Product::factory()->create(['precio_venta' => 5000, 'stock' => 2]);

        $initialStock = $product->stock;

        // Try to create sale with insufficient stock (should fail)
        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'cantidad' => 5], // > 2 available
            ],
        ]);

        // Verify stock wasn't decremented (transaction rolled back)
        $product->refresh();
        expect($product->stock)->toBe($initialStock);
    });
});
