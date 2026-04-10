<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    /**
     * Listar alertas
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::with('product')->orderBy('created_at', 'desc');

        if ($request->boolean('no_leidas_only')) {
            $query->noLeidas();
        }

        if ($request->boolean('criticas_only')) {
            $query->criticas();
        }

        if ($request->boolean('warnings_only')) {
            $query->warnings();
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->string('product_id'));
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->string('nivel'));
        }

        $perPage = $request->integer('per_page', 20);
        $alerts = $query->paginate($perPage);

        return response()->json($alerts);
    }

    /**
     * Obtener detalles de una alerta
     */
    public function show(Alert $alert): JsonResponse
    {
        $alert->load('product');

        return response()->json($alert);
    }

    /**
     * Marcar alerta como leída
     */
    public function markAsRead(Alert $alert): JsonResponse
    {
        $alert->marcarLeida();

        return response()->json([
            'message' => 'Alerta marcada como leída',
            'data' => $alert,
        ]);
    }

    /**
     * Marcar todas las alertas como leídas
     */
    public function markAllAsRead(): JsonResponse
    {
        Alert::noLeidas()->update(['leido' => true]);

        return response()->json([
            'message' => 'Todas las alertas marcadas como leídas',
        ]);
    }

    /**
     * Contar alertas no leídas
     */
    public function unreadCount(): JsonResponse
    {
        $count = Alert::noLeidas()->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Obtener resumen de alertas
     */
    public function summary(): JsonResponse
    {
        $summary = [
            'total_alertas' => Alert::count(),
            'no_leidas' => Alert::noLeidas()->count(),
            'criticas' => Alert::criticas()->count(),
            'warnings' => Alert::warnings()->count(),
            'por_tipo' => Alert::selectRaw('tipo, COUNT(*) as cantidad')
                               ->groupBy('tipo')
                               ->get(),
        ];

        return response()->json($summary);
    }

    /**
     * Eliminar alertas leídas
     */
    public function deleteRead(): JsonResponse
    {
        $deleted = Alert::where('leido', true)->delete();

        return response()->json([
            'message' => "Se eliminaron $deleted alertas leídas",
        ]);
    }
}
