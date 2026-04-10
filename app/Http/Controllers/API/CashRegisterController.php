<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashRegisterOpenRequest;
use App\Http\Requests\CashRegisterCloseRequest;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CashRegisterController extends Controller
{
    /**
     * Listar todas las cajas registradoras
     */
    public function index(Request $request): JsonResponse
    {
        $query = CashRegister::with('user', 'sales');

        if ($request->boolean('abiertas_only')) {
            $query->abierta();
        }

        if ($request->boolean('cerradas_only')) {
            $query->cerrada();
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->string('user_id'));
        }

        $perPage = $request->integer('per_page', 20);
        $cashRegisters = $query->orderBy('fecha_apertura', 'desc')
                               ->paginate($perPage);

        return response()->json($cashRegisters);
    }

    /**
     * Abrir una nueva caja registradora
     */
    public function open(CashRegisterOpenRequest $request): JsonResponse
    {
        // Validar que no haya otra caja abierta para este usuario
        $openRegister = CashRegister::where('user_id', auth()->user()->id)
                                    ->abierta()
                                    ->first();

        if ($openRegister) {
            return response()->json([
                'message' => 'Ya tienes una caja abierta. Debes cerrarla primero.',
                'data' => $openRegister,
            ], 422);
        }

        $cashRegister = CashRegister::create([
            'user_id' => auth()->user()->id,
            'monto_apertura' => $request->input('monto_apertura'),
            'total_ventas' => 0,
            'monto_esperado' => $request->input('monto_apertura'),
            'estado' => 'abierta',
            'fecha_apertura' => now(),
        ]);

        return response()->json([
            'message' => 'Caja abierta exitosamente',
            'data' => $cashRegister,
        ], 201);
    }

    /**
     * Obtener detalles de una caja registradora
     */
    public function show(CashRegister $cashRegister): JsonResponse
    {
        $cashRegister->load('user', 'sales');

        return response()->json($cashRegister);
    }

    /**
     * Cerrar una caja registradora
     */
    public function close(CashRegisterCloseRequest $request, CashRegister $cashRegister): JsonResponse
    {
        // Validar que pertenezca al usuario autenticado
        if ($cashRegister->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para cerrar esta caja',
            ], 403);
        }

        // Validar que esté abierta
        if (!$cashRegister->estaAbierta()) {
            return response()->json([
                'message' => 'Esta caja ya está cerrada',
            ], 422);
        }

        $cashRegister->cerrar(
            $request->input('dinero_contado'),
            $request->string('observacion')
        );

        return response()->json([
            'message' => 'Caja cerrada exitosamente',
            'data' => $cashRegister,
        ]);
    }

    /**
     * Obtener la caja abierta del usuario actual
     */
    public function myOpenRegister(): JsonResponse
    {
        $cashRegister = CashRegister::where('user_id', auth()->user()->id)
                                    ->abierta()
                                    ->with('sales')
                                    ->first();

        if (!$cashRegister) {
            return response()->json([
                'message' => 'No tienes una caja abierta',
            ], 404);
        }

        return response()->json($cashRegister);
    }

    /**
     * Resumen de arqueo de caja
     */
    public function summary(CashRegister $cashRegister): JsonResponse
    {
        if ($cashRegister->user_id !== auth()->user()->id && auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'No tienes permiso para ver este resumen',
            ], 403);
        }

        $summary = [
            'monto_apertura' => $cashRegister->monto_apertura,
            'total_ventas' => $cashRegister->total_ventas,
            'monto_esperado' => $cashRegister->monto_esperado,
            'dinero_contado' => $cashRegister->dinero_contado,
            'diferencia' => $cashRegister->diferencia,
            'estado' => $cashRegister->estado,
            'cantidad_transacciones' => $cashRegister->sales()->count(),
            'fecha_apertura' => $cashRegister->fecha_apertura,
            'fecha_cierre' => $cashRegister->fecha_cierre,
        ];

        return response()->json($summary);
    }
}
