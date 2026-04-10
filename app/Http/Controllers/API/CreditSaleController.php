<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreditSaleRequest;
use App\Http\Requests\CreditPaymentRequest;
use App\Models\CreditSale;
use App\Models\CreditSaleItem;
use App\Models\CreditPayment;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreditSaleController extends Controller
{
    /**
     * Listar ventas a crédito
     */
    public function index(Request $request): JsonResponse
    {
        $query = CreditSale::with('customer', 'items.product', 'payments')
                           ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->string('customer_id'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->boolean('pendientes')) {
            $query->pendientes();
        }

        $perPage = $request->integer('per_page', 20);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    /**
     * Crear una nueva venta a crédito
     */
    public function store(StoreCreditSaleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // 1. Obtener cliente (ANTES de transacción para validaciones)
        $customer = \App\Models\Customer::findOrFail($validated['customer_id']);

        // 2. Validar que cliente esté activo (ANTES de transacción)
        if (!$customer->puedeComprarACredito(0)) {
            return response()->json([
                'message' => 'Cliente inactivo',
                'status' => 422,
            ], 422);
        }

        return DB::transaction(function () use ($validated, $customer) {
            // 3. Calcular total de la compra
            $items = $validated['items'];
            $total = 0;
            $productDetails = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Validar stock
                if (!$product->tieneStockSuficiente($item['cantidad'])) {
                    throw new \DomainException(
                        "Stock insuficiente para {$product->nombre}. "
                        . "Disponible: {$product->stock}, Solicitado: {$item['cantidad']}"
                    );
                }

                $subtotal = $product->precio_venta * $item['cantidad'];
                $total += $subtotal;

                $productDetails[] = [
                    'product' => $product,
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $subtotal,
                ];
            }

            // 4. Crear venta a crédito
            $creditSale = CreditSale::create([
                'customer_id' => $validated['customer_id'],
                'total' => $total,
                'total_pagado' => 0,
                'saldo_pendiente' => $total,
                'estado' => 'abierta',
            ]);

            // 5. Procesar cada item
            foreach ($productDetails as $detail) {
                $product = $detail['product'];
                $cantidad = $detail['cantidad'];
                $subtotal = $detail['subtotal'];

                // Crear detalle
                CreditSaleItem::create([
                    'credit_sale_id' => $creditSale->id,
                    'product_id' => $product->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $product->precio_venta,
                    'subtotal' => $subtotal,
                ]);

                // ⭐ DESCUENTO INMEDIATO DE STOCK (aunque no se pagó)
                $product->decrement('stock', $cantidad);

                // Registrar movimiento
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'motivo' => "Venta a crédito #{$creditSale->id} - {$customer->nombre}",
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                ]);

                // Generar alertas
                if ($product->tieneStockBajo()) {
                    Alert::generarStockBajo($product);
                } elseif ($product->estaAgotado()) {
                    Alert::generarStockAgotado($product);
                }
            }

            // 6. Actualizar deuda del cliente
            $customer->update([
                'saldo_deuda' => $customer->saldo_deuda + $total,
                'ultima_compra_at' => now(),
            ]);

            return response()->json([
                'message' => 'Venta a crédito registrada exitosamente',
                'data' => $creditSale->load('customer', 'items.product'),
            ], 201);
        });
    }

    /**
     * Ver detalles de una venta a crédito
     */
    public function show(CreditSale $creditSale): JsonResponse
    {
        $creditSale->load('customer', 'items.product', 'payments');

        return response()->json([
            'data' => $creditSale,
        ]);
    }

    /**
     * Registrar un pago (abono) a una venta a crédito
     */
    public function registerPayment(CreditSale $creditSale, CreditPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $monto = (float) $validated['monto'];

        // Validar que hay saldo pendiente (ANTES de transacción)
        if ($creditSale->saldo_pendiente <= 0) {
            return response()->json([
                'message' => 'Esta venta ya está completamente pagada',
            ], 422);
        }

        // Validar monto (ANTES de transacción)
        if ($monto > $creditSale->saldo_pendiente) {
            return response()->json([
                'message' => 'El monto excede el saldo pendiente de ' . $creditSale->saldo_pendiente,
            ], 422);
        }

        return DB::transaction(function () use ($creditSale, $validated, $monto) {
            // 1. Registrar pago
            CreditPayment::create([
                'credit_sale_id' => $creditSale->id,
                'monto' => $monto,
                'metodo_pago' => $validated['metodo_pago'],
                'observacion' => $validated['observacion'] ?? null,
                'created_at' => now(),
            ]);

            // 2. Procesar abono
            $creditSale->pagarAbono($monto);

            return response()->json([
                'message' => 'Pago registrado correctamente',
                'data' => [
                    'monto_pagado' => $monto,
                    'saldo_pendiente' => $creditSale->saldo_pendiente,
                    'estado' => $creditSale->estado,
                ],
            ]);
        });
    }

    /**
     * Historial de pagos de una venta
     */
    public function payments(CreditSale $creditSale): JsonResponse
    {
        $payments = $creditSale->payments()
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json([
            'credit_sale_id' => $creditSale->id,
            'total_venta' => $creditSale->total,
            'total_pagado' => $creditSale->total_pagado,
            'saldo_pendiente' => $creditSale->saldo_pendiente,
            'pagos' => $payments,
        ]);
    }
}
