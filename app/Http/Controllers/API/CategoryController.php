<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
        }

        if ($request->boolean('activos_only')) {
            $query->activo();
        }

        // Ordenamiento
        $orderBy = $request->string('order_by', 'created_at');
        $orderDir = $request->string('order', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->integer('per_page', 15);
        $categories = $query->paginate($perPage);

        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        logger()->info('Categoría creada: ' . $category->id);

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();

        // Solo actualiza los campos que están presentes
        $category->update(array_filter($data, fn($value) => !is_null($value)));

        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        // Desagrupa los productos antes de eliminar
        $category->products()->update(['category_id' => null]);
        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente',
        ]);
    }

    /**
     * Obtener categorías con conteo de productos
     */
    public function stats(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'total' => Category::count(),
            'activas' => Category::activo()->count(),
            'categories' => $categories,
        ]);
    }

    /**
     * Obtener categoría con sus productos
     */
    public function withProducts(Category $category, Request $request): JsonResponse
    {
        $query = $category->products();

        if ($request->boolean('activos_only')) {
            $query->activo();
        }

        $perPage = $request->integer('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'category' => $category,
            'products' => $products,
        ]);
    }
}
