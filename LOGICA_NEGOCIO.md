# Estructura de la Lógica de Negocio - POS API 2

## 📁 Archivos Creados

### Form Requests (Validación)
```
app/Http/Requests/
├── StoreProductRequest.php          ✅ Validar creación de productos
├── UpdateProductRequest.php         ✅ Validar actualización de productos
├── StoreSaleRequest.php             ✅ Validar creación de ventas
├── CashRegisterOpenRequest.php      ✅ Validar apertura de caja
├── CashRegisterCloseRequest.php     ✅ Validar cierre de caja
├── StoreInventoryMovementRequest.php ✅ Validar movimientos de inventario
└── StoreUserRequest.php             ✅ Validar creación de usuarios
```

### Controladores API
```
app/Http/Controllers/API/
├── ProductController.php             ✅ CRUD de productos + búsqueda + estadísticas
├── SaleController.php                ✅ Crear ventas, listar, reportes
├── CashRegisterController.php        ✅ Abrir/cerrar cajas, arqueo
├── InventoryMovementController.php   ✅ Registrar movimientos de stock
├── AlertController.php               ✅ Gestión de alertas
└── UserController.php                ✅ CRUD de usuarios
```

### Policies (Autorización)
```
app/Policies/
├── ProductPolicy.php                 ✅ Solo admin puede crear/editar/eliminar
└── UserPolicy.php                    ✅ Solo admin puede gestionar usuarios
```

### Routes
```
routes/
└── api.php                           ✅ Todas las rutas API
```

---

## 🔧 Características Principales

### 🛍️ Productos
- [x] CRUD completo
- [x] Búsqueda por nombre o código de barras
- [x] Filtrar por estado (activos, stock bajo, agotados)
- [x] Estadísticas: total, valor inventario, etc
- [x] Validación de precios y stock

### 💳 Ventas
- [x] Crear venta con múltiples items
- [x] Validación de stock antes de venta
- [x] Transacciones atómicas
- [x] Auto-generación de alertas
- [x] Reportes por método de pago
- [x] Listado con filtros por fecha/método/usuario
- [x] Cálculo automático de ganancias

### 🏧 Cajas Registradoras
- [x] Abrir caja (una por usuario)
- [x] Cerrar caja con arqueo
- [x] Calcular diferencias
- [x] Listar transacciones por caja
- [x] Resumen de arqueo

### 📦 Movimientos de Inventario
- [x] Registrar entradas/salidas/ajustes
- [x] Validación de stock en salidas
- [x] Historial completo
- [x] Resumen por tipo
- [x] Trazabilidad por usuario

### ⚠️ Alertas Automáticas
- [x] Stock bajo
- [x] Stock agotado
- [x] Marcar como leídas
- [x] Filtrar por nivel (info/warning/critical)
- [x] Resumen de alertas

### 👥 Usuarios
- [x] CRUD
- [x] Roles: admin y cajero
- [x] Hash de contraseñas
- [x] Búsqueda
- [x] Estadísticas

---

## 🔐 Lógica de Seguridad

#### Autenticación
- Sanctum para tokens
- Middleware `auth:sanctum` en todas las rutas

#### Autorización
- Solo admins pueden crear/editar/eliminar productos
- Solo admins pueden crear usuarios y cambiar roles
- Cada usuario solo puede operar su propia caja
- Cada usuario solo puede ver/editar su perfil (excepto admins)

#### Validación de Negocio
- Una sola caja abierta por usuario
- Stock suficiente antes de registrar venta
- Caja debe estar abierta para registrar venta
- Transacciones atómicas con DB::transaction()

---

## 📊 Flujo de Ventas

```
1. Cajero abre caja → POST /api/cash-registers/open
2. Cajero crea venta → POST /api/sales
   - Valida caja abierta
   - Valida stock de productos
   - Crea venta + detalles
   - Decrementa stock
   - Genera alertas si es necesario
   - Acumula total en caja
3. Cajero cierra caja → POST /api/cash-registers/{id}/close
   - Calcula diferencia
   - Registra dinero contado
```

---

## 📝 Notas Técnicas

### Modelos Usados
- Product (con métodos helpers)
- Sale (con scopes)
- SaleDetail (con factory method)
- CashRegister (con métodos helpers)
- InventoryMovement (con métodos estáticos)
- Alert (con factory methods)
- User (con HasUuids)

### Parámetros de Paginación
- `/api/products` → default 15 items
- Otros endpoints → default 20 items
- Personalizable con `?per_page=50`

### Transacciones
- Las ventas usan `DB::transaction()` para garantizar consistencia
- Si algo falla, se revierte toda la operación

### Errores Conocidos del Linter
- Pylance no reconoce `auth()->user()` e `auth()->id()` como válidos
- Esto es un falso positivo, los métodos de Laravel funcionan correctamente

---

## 🚀 Próximos Pasos

1. Crear autenticación (login/register)
2. Tests con Pest
3. Seeding con datos de ejemplo
4. Documentación Swagger/OpenAPI
5. Rate limiting
6. Logs de auditoría
