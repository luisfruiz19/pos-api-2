<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with('category');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_barras', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->string('category_id'));
        }

        if ($request->boolean('activos_only')) {
            $query->activo();
        }

        if ($request->boolean('stock_bajo_only')) {
            $query->stockBajo();
        }

        if ($request->boolean('agotados_only')) {
            $query->agotado();
        }

        // Ordenamiento
        $orderBy = $request->string('order_by', 'created_at');
        $orderDir = $request->string('order', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->integer('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['imagen'])) {
            $data['imagen'] = $this->storeBase64ProductImage($data['imagen']);
        }

        $product = Product::create($data);
        logger()->info('Producto creado: ' . $product->id);
        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['imagen'])) {
            $data['imagen'] = $this->storeBase64ProductImage($data['imagen']);
        }

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente',
        ]);
    }

    /**
     * Obtener productos con stock bajo
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::stockBajo()->get();

        return response()->json($products);
    }

    /**
     * Obtener productos agotados
     */
    public function outOfStock(): JsonResponse
    {
        $products = Product::agotado()->get();

        return response()->json($products);
    }

    /**
     * Estadísticas de productos
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => Product::count(),
            'activos' => Product::activo()->count(),
            'stock_bajo' => Product::stockBajo()->count(),
            'agotados' => Product::agotado()->count(),
            'valor_inventario' => Product::where('activo', true)
                ->selectRaw('SUM(stock * precio_compra) as total')
                ->value('total') ?? 0,
        ]);
    }

    /**
     * Estadísticas detalladas de productos
     */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'total_productos' => Product::count(),
            'stock_bajo' => Product::stockBajo()->count(),
            'sin_stock' => Product::agotado()->count(),
            'stock_total' => Product::sum('stock'),
        ]);
    }

    private function storeBase64ProductImage(string $value): string
    {
        $base64 = trim($value);

        if (Str::startsWith($base64, 'data:')) {
            $commaPos = strpos($base64, ',');
            $base64 = $commaPos === false ? $base64 : substr($base64, $commaPos + 1);
        }

        // Normaliza Base64 que viene de algunos clientes móviles (espacios en vez de '+', saltos de línea, etc.)
        $base64 = str_replace(' ', '+', $base64);
        $base64 = preg_replace('/\s+/', '', $base64) ?? $base64;

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            throw new \DomainException('Imagen inválida');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary) ?: null;

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if ($extension === null) {
            throw new \DomainException('Tipo de imagen no permitido');
        }

        $filename = 'products/' . Str::uuid()->toString() . '.' . $extension;
        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }
}
