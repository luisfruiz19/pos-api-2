<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\CreditSale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Listar clientes con deuda
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query()
                         ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->boolean('con_deuda')) {
            $query->conDeuda();
        }

        if ($request->boolean('morosos')) {
            $query->morosos();
        }

        $perPage = $request->integer('per_page', 20);
        $customers = $query->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * Crear un nuevo cliente
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $customer = Customer::create([
            'nombre' => $validated['nombre'],
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'estado' => 'activo',
        ]);

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'data' => $customer,
        ], 201);
    }

    /**
     * Obtener detalles del cliente
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load('creditSales.items.product', 'creditSales.payments');

        return response()->json([
            'data' => $customer,
        ]);
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'min:3', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'limite_credito' => ['sometimes', 'numeric', 'min:1000'],
            'estado' => ['sometimes', 'in:activo,inactivo,incobrable'],
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado',
            'data' => $customer,
        ]);
    }

    /**
     * Obtener historial de compras a crédito del cliente
     */
    public function creditHistory(Customer $customer, Request $request): JsonResponse
    {
        $query = $customer->creditSales()
                          ->with('items.product', 'payments')
                          ->orderBy('created_at', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        $perPage = $request->integer('per_page', 20);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    /**
     * Resumen de deuda del cliente
     */
    public function summary(Customer $customer): JsonResponse
    {
        $ventasAbiertas = $customer->creditSales()
            ->pendientes()
            ->count();

        $ventasPagadas = $customer->creditSales()
            ->pagadas()
            ->count();

        return response()->json([
            'customer' => $customer,
            'resumen' => [
                'saldo_deuda' => $customer->saldo_deuda,
                'ventas_abiertas' => $ventasAbiertas,
                'ventas_pagadas' => $ventasPagadas,
                'estado' => $customer->estado,
            ],
        ]);
    }
}
