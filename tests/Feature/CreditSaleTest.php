<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CreditSale;
use App\Models\CreditPayment;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($this->user);
});

describe('Ventas a Crédito - CRUD', function () {
    it('puede crear una venta a crédito', function () {
        $customer = Customer::factory()->create([
            'saldo_deuda' => 0,
            'limite_credito' => 100000,
            'estado' => 'activo',
        ]);

        $product = Product::factory()->create([
            'precio_venta' => 10000,
            'stock' => 100,
        ]);

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'cantidad' => 5,
                ],
            ],
        ]);

        $response->assertStatus(201);
        expect((float) $response->json('data.total'))->toBe(50000.0);
        $response->assertJsonPath('data.estado', 'abierta');

        // Verificar stock decrementado
        expect($product->fresh()->stock)->toBe(95);
    });

    it('no permite crear venta a crédito sin stock suficiente', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'cantidad' => 10,
                ],
            ],
        ]);

        $response->assertStatus(422);
        expect($product->fresh()->stock)->toBe(2);
    });

    it('no permite venta si excede límite de crédito', function () {
        $customer = Customer::factory()->create([
            'saldo_deuda' => 90000,
            'limite_credito' => 100000,
        ]);

        $product = Product::factory()->create([
            'precio_venta' => 15000,
            'stock' => 100,
        ]);

        $response = $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'cantidad' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
    });

    it('puede listar ventas a crédito', function () {
        $customer = Customer::factory()->create();
        CreditSale::factory()->create(['customer_id' => $customer->id]);

        $response = $this->getJson('/api/credit-sales');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.customer_id', $customer->id);
    });

    it('puede ver detalles de una venta a crédito', function () {
        $customer = Customer::factory()->create();
        $creditSale = CreditSale::factory()->create(['customer_id' => $customer->id]);

        $response = $this->getJson("/api/credit-sales/{$creditSale->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('customer_id', $customer->id);
    });
});

describe('Pagos a Crédito', function () {
    it('puede registrar un pago parcial', function () {
        $customer = Customer::factory()->create([
            'saldo_deuda' => 50000,
            'limite_credito' => 100000,
        ]);

        $creditSale = CreditSale::factory()->create([
            'customer_id' => $customer->id,
            'total' => 50000,
            'total_pagado' => 0,
            'saldo_pendiente' => 50000,
            'estado' => 'abierta',
        ]);

        $response = $this->postJson("/api/credit-sales/{$creditSale->id}/payment", [
            'monto' => 20000,
            'metodo_pago' => 'efectivo',
            'observacion' => 'Abono de Don Juan',
        ]);

        $response->assertStatus(200);
        expect((float) $response->json('data.saldo_pendiente'))->toBe(30000.0);
        $response->assertJsonPath('data.estado', 'parcial');

        // Verificar que la deuda del cliente se actualizó
        expect($customer->fresh()->saldo_deuda)->toBe(30000);
    });

    it('puede completar el pago de una venta a crédito', function () {
        $customer = Customer::factory()->create([
            'saldo_deuda' => 50000,
        ]);

        $creditSale = CreditSale::factory()->create([
            'customer_id' => $customer->id,
            'total' => 50000,
            'total_pagado' => 20000,
            'saldo_pendiente' => 30000,
            'estado' => 'parcial',
        ]);

        $response = $this->postJson("/api/credit-sales/{$creditSale->id}/payment", [
            'monto' => 30000,
            'metodo_pago' => 'transferencia',
        ]);

        $response->assertStatus(200);
        expect((float) $response->json('data.saldo_pendiente'))->toBe(0.0);
        $response->assertJsonPath('data.estado', 'pagada');

        expect((float) $customer->fresh()->saldo_deuda)->toBe(0.0);
    });

    it('no permite pago mayor al saldo pendiente', function () {
        $creditSale = CreditSale::factory()->create([
            'total' => 50000,
            'saldo_pendiente' => 30000,
        ]);

        $response = $this->postJson("/api/credit-sales/{$creditSale->id}/payment", [
            'monto' => 50000,
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertStatus(422);
    });

    it('puede listar historial de pagos', function () {
        $creditSale = CreditSale::factory()->create();
        CreditPayment::factory()->create(['credit_sale_id' => $creditSale->id]);

        $response = $this->getJson("/api/credit-sales/{$creditSale->id}/payments");

        $response->assertStatus(200);
        expect($response->json('pagos'))->toHaveCount(1);
    });
});

describe('Stock y Deuda', function () {
    it('el stock se decrementa inmediatamente aunque el pago esté pendiente', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'precio_venta' => 10000,
            'stock' => 50,
        ]);

        // Venta a crédito
        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'cantidad' => 20,
                ],
            ],
        ]);

        // El stock debe estar ya decrementado
        expect($product->fresh()->stock)->toBe(30);

        // El cliente debe tener deuda
        expect((float) $customer->fresh()->saldo_deuda)->toBe(200000.0);
    });

    it('múltiples compras a crédito acumulan deuda', function () {
        $customer = Customer::factory()->create([
            'saldo_deuda' => 0,
            'limite_credito' => 500000,
        ]);

        $product = Product::factory()->create([
            'precio_venta' => 10000,
            'stock' => 500,
        ]);

        // Primer compra
        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'cantidad' => 10,
                ],
            ],
        ]);

        expect((float) $customer->fresh()->saldo_deuda)->toBe(100000.0);

        // Segunda compra
        $this->postJson('/api/credit-sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'cantidad' => 15,
                ],
            ],
        ]);

        expect((float) $customer->fresh()->saldo_deuda)->toBe(250000.0);
    });
});
