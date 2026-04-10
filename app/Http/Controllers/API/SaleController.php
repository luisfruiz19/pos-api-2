<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\CashRegister;
use App\Models\InventoryMovement;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Listar ventas con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::query()->with(['user', 'cashRegister', 'details.product'])
                     ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->string('user_id'));
        }

        if ($request->filled('cash_register_id')) {
            $query->where('cash_register_id', $request->string('cash_register_id'));
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->string('metodo_pago'));
        }

        if ($request->boolean('hoy')) {
            $query->hoy();
        }

        if ($request->boolean('este_mes')) {
            $query->esteMes();
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $inicio = \Carbon\Carbon::parse($request->string('fecha_inicio'))->startOfDay();
            $fin = \Carbon\Carbon::parse($request->string('fecha_fin'))->endOfDay();

            $query->whereBetween('created_at', [$inicio, $fin]);
        }

        $perPage = $request->integer('per_page', 20);
        $sales = $query->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        return response()->json($sales);
    }

    /**
     * Crear una nueva venta
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Validar que la caja esté abierta
        $cashRegister = CashRegister::findOrFail($validated['cash_register_id']);
        if (!$cashRegister->estaAbierta()) {
            return response()->json([
                'message' => 'La caja registradora debe estar abierta para registrar ventas',
            ], 422);
        }

        // Validar que la caja pertenezca al usuario autenticado
        if ($cashRegister->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Solo puedes registrar ventas en tu propia caja',
            ], 403);
        }

        try {
            $sale = DB::transaction(function () use ($validated, $cashRegister) {
                $items = $validated['items'];
                $total = 0;
                $gananciaTotal = 0;
                $saleDetails = [];

                // Procesar cada item y validar stock
                foreach ($items as $item) {
                    $product = \App\Models\Product::findOrFail($item['product_id']);
                    logger()->info("Producto encontrado: {$product->nombre} (ID: {$product->id}) con stock {$product->stock}");
                    logger()->info("Cantidad solicitada: {$item['cantidad']}");
                    if (!$product->tieneStockSuficiente($item['cantidad'])) {
                        throw new \Exception(
                            "Stock insuficiente para {$product->nombre}. "
                            . "Disponible: {$product->stock}, Solicitado: {$item['cantidad']}"
                        );
                    }

                    $detail = SaleDetail::fromProduct($product, $item['cantidad']);
                    $total += $detail->subtotal;
                    $gananciaTotal += $detail->ganancia;
                    $saleDetails[] = $detail;
                }

                // Crear la venta
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'cash_register_id' => $validated['cash_register_id'],
                    'total' => $total,
                    'ganancia_total' => $gananciaTotal,
                    'metodo_pago' => $validated['metodo_pago'],
                ]);

                // Asociar detalles y actualizar stock
                foreach ($saleDetails as $detail) {
                    $detail->sale_id = $sale->id;
                    $detail->save();

                    $product = $detail->product;
                    InventoryMovement::registrarSalida(
                        $product,
                        $detail->cantidad,
                        auth()->user(),
                        'Venta'
                    );

                    // Generar alertas si es necesario
                    if ($product->tieneStockBajo()) {
                        Alert::generarStockBajo($product);
                    } elseif ($product->estaAgotado()) {
                        Alert::generarStockAgotado($product);
                    }
                }

                // Acumular venta en la caja
                $cashRegister->acumularVenta($total);

                return $sale;
            });

            return response()->json([
                'message' => 'Venta registrada exitosamente',
                'data' => $sale->load('details.product'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener detalles de una venta
     */
    public function show(Sale $sale): JsonResponse
    {
        $sale->load('user', 'cashRegister', 'details.product');

        return response()->json($sale);
    }

    /**
     * Reportes de ventas
     */
    public function report(Request $request): JsonResponse
    {
        $query = Sale::query();

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $inicio = \Carbon\Carbon::parse($request->string('fecha_inicio'))->startOfDay();
            $fin = \Carbon\Carbon::parse($request->string('fecha_fin'))->endOfDay();

            $query->whereBetween('created_at', [$inicio, $fin]);
        }

        $report = [
            'total_ventas' => $query->count(),
            'ingresos_totales' => $query->sum('total'),
            'ganancia_total' => $query->sum('ganancia_total'),
            'por_metodo_pago' => DB::table('sales')
                ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total) as total')
                ->groupBy('metodo_pago')
                ->get(),
        ];

        return response()->json($report);
    }
}
