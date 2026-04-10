<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryMovementRequest;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryMovementController extends Controller
{
    /**
     * Listar movimientos de inventario
     */
    public function index(Request $request): JsonResponse
    {
        $query = InventoryMovement::with('product', 'user')
                                  ->orderBy('created_at', 'desc');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->string('product_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->string('user_id'));
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->string('fecha_inicio'),
                $request->string('fecha_fin'),
            ]);
        }

        $perPage = $request->integer('per_page', 20);
        $movements = $query->paginate($perPage);

        return response()->json($movements);
    }

    /**
     * Registrar un nuevo movimiento de inventario
     */
    public function store(StoreInventoryMovementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $product = \App\Models\Product::findOrFail($validated['product_id']);

        try {
            if ($validated['tipo'] === 'entrada') {
                $movement = InventoryMovement::registrarEntrada(
                    $product,
                    $validated['cantidad'],
                    auth()->user(),
                    $validated['motivo']
                );
            } elseif ($validated['tipo'] === 'salida') {
                $movement = InventoryMovement::registrarSalida(
                    $product,
                    $validated['cantidad'],
                    auth()->user(),
                    $validated['motivo']
                );
            } else {
                // Ajuste
                $movement = InventoryMovement::create([
                    'product_id' => $validated['product_id'],
                    'tipo' => 'ajuste',
                    'cantidad' => $validated['cantidad'],
                    'motivo' => $validated['motivo'],
                    'user_id' => auth()->user()->id,
                    'created_at' => now(),
                ]);

                if ($validated['cantidad'] > 0) {
                    $product->increment('stock', $validated['cantidad']);
                } else {
                    $product->decrement('stock', abs($validated['cantidad']));
                }
            }

            return response()->json([
                'message' => 'Movimiento registrado exitosamente',
                'data' => $movement->load('product', 'user'),
            ], 201);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener detalles de un movimiento
     */
    public function show(InventoryMovement $inventoryMovement): JsonResponse
    {
        $inventoryMovement->load('product', 'user');

        return response()->json($inventoryMovement);
    }

    /**
     * Movimientos por producto
     */
    public function byProduct(Request $request, string $productId): JsonResponse
    {
        $movements = InventoryMovement::where('product_id', $productId)
                                      ->with('product', 'user')
                                      ->orderBy('created_at', 'desc')
                                      ->paginate($request->integer('per_page', 20));

        return response()->json($movements);
    }

    /**
     * Resumen de movimientos
     */
    public function summary(Request $request): JsonResponse
    {
        $query = InventoryMovement::query();

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->string('fecha_inicio'),
                $request->string('fecha_fin'),
            ]);
        }

        $summary = [
            'entradas' => $query->clone()->entradas()->count(),
            'salidas' => $query->clone()->salidas()->count(),
            'ajustes' => $query->clone()->ajustes()->count(),
            'total_movimientos' => $query->count(),
        ];

        return response()->json($summary);
    }
}
