# API de Categorías - Resumen de Implementación

## ✅ Completado

### 1. Modelo & Base de Datos
- ✅ Modelo `Category` en [app/Models/Category.php](app/Models/Category.php)
- ✅ Migración de categorías: [database/migrations/2023_01_01_180000_create_categories_table.php](database/migrations/2023_01_01_180000_create_categories_table.php)
- ✅ Actualización de `products` con `category_id` (foreign key)
- ✅ Relación `Product::belongsTo(Category)` y `Category::hasMany(Product)`

### 2. Controlador & Validación
- ✅ [app/Http/Controllers/API/CategoryController.php](app/Http/Controllers/API/CategoryController.php)
- ✅ [app/Http/Requests/StoreCategoryRequest.php](app/Http/Requests/StoreCategoryRequest.php)
- ✅ [app/Http/Requests/UpdateCategoryRequest.php](app/Http/Requests/UpdateCategoryRequest.php)
- ✅ Actualización de validaciones en ProductRequest para incluir `category_id`

### 3. Rutas Disponibles
```
GET    /api/categories                    - Listar categorías (con filtros y paginación)
POST   /api/categories                    - Crear categoría
GET    /api/categories/stats              - Estadísticas de categorías
GET    /api/categories/{category}         - Obtener categoría
PUT    /api/categories/{category}         - Actualizar categoría
DELETE /api/categories/{category}         - Eliminar categoría
GET    /api/categories/{category}/products - Obtener productos de una categoría
```

### 4. Datos de Prueba
- ✅ [database/factories/CategoryFactory.php](database/factories/CategoryFactory.php)
- ✅ [database/seeders/CategorySeeder.php](database/seeders/CategorySeeder.php)
- ✅ [database/seeders/ProductSeeder.php](database/seeders/ProductSeeder.php) - Actualizado para asociar categorías
- ✅ 8 categorías creadas automáticamente
- ✅ 20 productos creados con categorías asociadas

### 5. Categorías por Defecto
1. Bebidas
2. Snacks
3. Comidas
4. Dulces
5. Lácteos
6. Panadería
7. Frutas
8. Verduras

## 📖 Ejemplo de Uso

### Crear Categoría
```bash
POST /api/categories
Content-Type: application/json

{
  "nombre": "Bebidas",
  "descripcion": "Bebidas frías y calientes",
  "activo": true
}
```

### Listar Categorías
```bash
GET /api/categories?order_by=nombre&per_page=10&activos_only=true
```

### Obtener Productos de una Categoría
```bash
GET /api/categories/{category_id}/products?per_page=15&activos_only=true
```

### Crear Producto con Categoría
```bash
POST /api/products
Content-Type: application/json

{
  "nombre": "Agua 500ml",
  "category_id": "{category_uuid}",
  "precio_compra": 0.50,
  "precio_venta": 1.00,
  "stock": 50,
  "stock_minimo": 10
}
```

### Actualizar Producto con Categoría
```bash
PUT /api/products/{product_id}
Content-Type: application/json

{
  "category_id": "{new_category_uuid}"
}
```

## 🧪 Ejecutar Migraciones y Seeders

```bash
# Migraciones limpias con dados de prueba
php artisan migrate:fresh --seed

# O solo migraciones
php artisan migrate

# O solo seeders
php artisan db:seed
```

## 📊 Respuesta de Estadísticas

```json
GET /api/categories/stats

{
  "total": 8,
  "activas": 8,
  "categories": [
    {
      "id": "uuid-1",
      "nombre": "Bebidas",
      "descripcion": "Bebidas frías y calientes",
      "activo": true,
      "products_count": 5,
      "created_at": "2026-04-06T20:41:00Z",
      "updated_at": "2026-04-06T20:41:00Z"
    }
  ]
}
```

## 🔑 Características Implementadas

✅ CRUD completo de categorías  
✅ Relación many-to-one con productos (Product → Category)  
✅ Filtros en listados (búsqueda, activos)  
✅ Estadísticas con conteo de productos  
✅ Paginación  
✅ Validación de datos  
✅ Datos de prueba por defecto  
✅ Eliminación en cascada con desagrupamiento seguro  
✅ Scopes locales para consultas reutilizables  
