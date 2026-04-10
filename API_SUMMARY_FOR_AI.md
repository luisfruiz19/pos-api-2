# POS API - Documentación Ejecutiva para Integración

## 📋 Resumen del Proyecto

Sistema de Punto de Venta (POS) con API REST desarrollada en **Laravel 11** con autenticación Sanctum. Incluye gestión de productos, ventas, cajas registradoras, inventario y alertas.

## 🔗 Endpoints Principales

### 1. Autenticación (Sin protección)
- `POST /auth/register` - Registrar nuevo usuario
- `POST /auth/login` - Iniciar sesión
- `POST /auth/logout` - Cerrar sesión (protegido)

### 2. Productos (Protegido)
- `GET /products` - Listar productos paginados
  - Filtros: `search`, `activos_only`, `stock_bajo_only`, `agotados_only`
  - Ordenamiento: `order_by`, `order`
  - Paginación: `per_page`
- `POST /products` - Crear producto (admin)
- `GET /products/{id}` - Detalles de producto
- `PUT /products/{id}` - Actualizar producto (admin)
- `DELETE /products/{id}` - Eliminar producto (admin)
- `GET /products/low-stock` - Productos con stock bajo
- `GET /products/out-of-stock` - Productos agotados
- `GET /products/stats` - Estadísticas (total, activos, stock_bajo, agotados, valor_inventario)

### 3. Ventas (Protegido)
- `GET /sales` - Listar ventas
  - Filtros: `user_id`, `cash_register_id`, `metodo_pago`, `hoy`, `este_mes`, `fecha_inicio`, `fecha_fin`
- `POST /sales` - Crear venta (require caja abierta)
  ```json
  {
    "cash_register_id": "uuid",
    "metodo_pago": "efectivo|yape|plin",
    "items": [
      {"product_id": "uuid", "cantidad": 2}
    ]
  }
  ```
- `GET /sales/{id}` - Detalles de venta
- `GET /sales/report` - Reporte de ventas por período

### 4. Cajas Registradoras (Protegido)
- `GET /cash-registers` - Listar cajas
- `POST /cash-registers/open` - Abrir caja
  ```json
  {"monto_apertura": 100.00}
  ```
- `GET /cash-registers/my-open-register` - Mi caja abierta
- `GET /cash-registers/{id}` - Detalles de caja
- `POST /cash-registers/{id}/close` - Cerrar caja
  ```json
  {
    "dinero_contado": 250.50,
    "observacion": "Diferencia de 5.50"
  }
  ```
- `GET /cash-registers/{id}/summary` - Resumen de caja

### 5. Movimientos de Inventario (Protegido)
- `GET /inventory-movements` - Listar movimientos
- `POST /inventory-movements` - Registrar movimiento (admin)
  ```json
  {
    "product_id": "uuid",
    "tipo": "entrada|salida|ajuste",
    "cantidad": 10,
    "motivo": "Descripción"
  }
  ```
- `GET /inventory-movements/{id}` - Detalles
- `GET /inventory-movements/by-product/{productId}` - Por producto
- `GET /inventory-movements/summary` - Resumen

### 6. Alertas (Protegido)
- `GET /alerts` - Listar alertas
  - Filtros: `no_leidas_only`, `criticas_only`, `nivel`, `product_id`
- `GET /alerts/{id}` - Detalles
- `POST /alerts/{id}/mark-as-read` - Marcar como leída
- `POST /alerts/mark-all-as-read` - Marcar todas como leídas
- `GET /alerts/unread-count` - Contar no leídas
- `DELETE /alerts/delete-read` - Eliminar leídas

### 7. Usuarios (Protegido)
- `GET /users/me` - Mi perfil
- `GET /users` - Listar usuarios (admin)
- `POST /users` - Crear usuario (admin)
- `GET /users/{id}` - Detalles usuario
- `PUT /users/{id}` - Actualizar usuario
- `DELETE /users/{id}` - Eliminar usuario (admin)
- `GET /users/stats` - Estadísticas (admin)

## 🔐 Autenticación

**Tipo:** Bearer Token (Sanctum)

**Header requerido:**
```
Authorization: Bearer {token}
```

**Token obtenido en:**
- Login: `POST /auth/login`
- Registro: `POST /auth/register`

## 📊 Estructura de Respuestas

### Éxito
```json
{
  "data": { /* datos */ },
  "message": "Descripción"
}
```

### Paginación
```json
{
  "data": [ /* items */ ],
  "current_page": 1,
  "per_page": 15,
  "total": 100,
  "last_page": 7
}
```

### Error
```json
{
  "message": "Descripción del error",
  "errors": {
    "campo": ["Mensaje de validación"]
  }
}
```

## 🛡️ Códigos de Respuesta

| Código | Significado |
|--------|-----------|
| 200 | OK - Petición exitosa |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Solicitud inválida |
| 401 | Unauthorized - No autenticado/token inválido |
| 403 | Forbidden - No autorizado para la acción |
| 404 | Not Found - Recurso no existe |
| 422 | Unprocessable Entity - Validación fallida |
| 500 | Server Error - Error del servidor |

## 🎯 Roles y Permisos

**Roles:** `admin`, `cajero`

- **Admin:**
  - Crear/Editar/Eliminar productos
  - Registrar movimientos de inventario
  - Crear/Editar/Eliminar usuarios
  - Ver estadísticas
  - Acceso a todas las funciones

- **Cajero:**
  - Ver productos
  - Crear ventas
  - Abrir/Cerrar caja registradora
  - Ver alertas

## 📁 Documentación Completa

| Archivo | Descripción |
|---------|-----------|
| `API_OPENAPI.json` | **OpenAPI/Swagger** - Especificación completa en formato estándar. Compatible con Postman, Swagger UI, Redoc |
| `API_FRONTEND_INTEGRATION.md` | Guía técnica para integración Frontend con ejemplos de Axios, Pinia, Vue 3 |
| `QUICK_START_FRONTEND.md` | Guía rápida con componentes Vue ejemplos listos para copiar |
| `API_DOCUMENTATION.md` | Documentación anterior en markdown |

## 🚀 Para Compartir con Otra IA

**Usa estoslados archivos archivos:**

1. **API_OPENAPI.json** - Pásalo a cualquier IA con instrucción:
   > "Eres un experto en integración de APIs. Aquí está la especificación OpenAPI del POS. Dame código para: [tu tarea]"

2. **API_FRONTEND_INTEGRATION.md** - Para tareas de Frontend con Vue/React:
   > "Necesito integrar un Frontend con esta API usando [su framework favorito]. Usa esta guía:"

3. **QUICK_START_FRONTEND.md** - Para generar componentes rápidamente

## 💻 Stack Tecnológico

**Backend:**
- Laravel 11
- PHP 8.3+
- SQLite/MySQL
- Sanctum (Autenticación)

**Frontend (Recomendado):**
- Vue 3 (Composition API)
- Axios (HTTP Client)
- Pinia (State Management)
- Vue Router (Routing)
- Vite (Build Tool)

## 📝 Variables de Entorno Frontend

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=POS System
```

## 🔄 Flujo Típico de Uso

### 1. Autenticación
```
POST /auth/login → Recibe token → Guarda en localStorage
```

### 2. Venta
```
GET /cash-registers/my-open-register → Obtiene caja abierta
GET /products → Busca productos
POST /sales → Crea venta con items
```

### 3. Cierre de Caja
```
GET /cash-registers/{id}/summary → Obtiene resumen
POST /cash-registers/{id}/close → Cierra caja
```

## 🔗 Relaciones de Datos

```
User (✓ Autenticado)
├── CashRegister (estados: abierta, cerrada)
│   └── Sale (métodos de pago: efectivo, yape, plin)
│       └── SaleDetail
│           └── Product
├── InventoryMovement (tipos: entrada, salida, ajuste)
│   └── Product
└── Alert (niveles: info, warning, critical)
    └── Product (nullable)
```

## ⚡ Casos de Uso Comunes

### Crear una Venta
1. Verificar caja abierta: `GET /cash-registers/my-open-register`
2. Buscar productos: `GET /products?search=xyz`
3. Crear venta: `POST /sales` con items
4. Sistema automáticamente: descontará stock, creará alertas

### Gestionar Stock
1. Listar productos bajo stock: `GET /products/low-stock`
2. Registrar entrada: `POST /inventory-movements` (tipo: entrada)
3. Registrar salida manual: `POST /inventory-movements` (tipo: salida)
4. Sistema genera alertas automáticamente

### Cerrar Operaciones del Día
1. Obtener resumen: `GET /cash-registers/{id}/summary`
2. Contar dinero físico
3. Registrar diferencia: `POST /cash-registers/{id}/close`
4. Sistema guarda discrepancias

## 🎨 Temas de Frontend

Se recomienda incluir:
- Dashboard con estadísticas
- Pantalla de login
- Listado y gestión de productos
- Módulo de ventas (POS)
- Gestión de cajas
- Reportes
- Alertas en tiempo real

## 📦 Archivos de Servicio Frontend Incluidos

Listos para usar en `resources/js/`:
- `services/authService.js` - Autenticación
- `services/productService.js` - Productos
- `services/saleService.js` - Ventas
- `services/cashRegisterService.js` - Cajas
- `services/alertService.js` - Alertas
- `services/inventoryService.js` - Inventario
- `services/userService.js` - Usuarios
- `stores/authStore.js` - Estado (Pinia)
- `stores/productStore.js` - Estado productos
- `api/http.js` - Cliente HTTP configurado
- `utils/helpers.js` - Funciones auxiliares
- `composables/index.js` - Composables Vue

## 🧪 Probar API

### Con cURL
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### Con Postman
1. Importar `API_OPENAPI.json`
2. Configurar variable de entorno: `token`
3. En Auth: Bearer Token = `{{token}}`

### Con client HTTP (VS Code)
Crear archivo `.http` con requests

## 📞 Soporte y Resolución de Problemas

**Error 401 (Unauthorized):**
- Token expirado o inválido
- Verificar header `Authorization`

**Error 422 (Unprocessable Entity):**
- Validación fallida
- Revisar `errors` en respuesta

**Error 403 (Forbidden):**
- Rol insuficiente
- Usuario no es admin

**Error 404 (Not Found):**
- Recurso no existe
- Verificar IDs en URL

## 📈 Próximos Pasos para IA Integradora

1. Usar `API_OPENAPI.json` como referencia principal
2. Para Frontend: Seguir `API_FRONTEND_INTEGRATION.md`
3. Para componentes rápidos: Usar templates en `QUICK_START_FRONTEND.md`
4. Asegurar manejo de errores 401 para re-autenticación
5. Implementar debounce en búsquedas
6. Cachear datos cuando sea posible

---

**Última actualización:** 2026-04-01
**Versión API:** 1.0.0
**Especificación:** OpenAPI 3.0.0
