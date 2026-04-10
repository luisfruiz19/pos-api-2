# Documentación API POS

## Autenticación
Todos los endpoints requieren autenticación via Sanctum. Incluye el token en el header:
```
Authorization: Bearer <token>
```

## Endpoints

### 🛍️ PRODUCTOS

#### Listar productos
```
GET /api/products
Parámetros:
  - search (string): Buscar por nombre o código de barras
  - activos_only (boolean): Solo productos activos
  - stock_bajo_only (boolean): Solo con stock bajo
  - agotados_only (boolean): Solo sin stock
  - order_by (string): Campo para ordenar (default: created_at)
  - order (string): asc|desc (default: desc)
  - per_page (integer): Resultados por página (default: 15)
```

#### Crear producto
```
POST /api/products
Body:
{
  "nombre": "Producto",
  "precio_compra": 10.00,
  "precio_venta": 15.00,
  "stock": 100,
  "stock_minimo": 2,
  "codigo_barras": "123456789",
  "imagen": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
  "activo": true
}
Requiere role: admin
```

Notas:
- `stock_minimo` es opcional; si no se envía, por defecto es `2`.
- `imagen` se envía como string Base64 (data URI o base64 plano). En BD se guarda la ruta del archivo generado y en respuestas el API devuelve la **URL absoluta**.
- Para servir imágenes: configurar `APP_URL` y ejecutar `php artisan storage:link`.

#### Obtener producto
```
GET /api/products/{id}
```

#### Actualizar producto
```
PUT /api/products/{id}
Requiere role: admin
```

#### Eliminar producto
```
DELETE /api/products/{id}
Requiere role: admin
```

#### Productos con stock bajo
```
GET /api/products/low-stock
```

#### Productos agotados
```
GET /api/products/out-of-stock
```

#### Estadísticas
```
GET /api/products/stats
Respuesta: { total, activos, stock_bajo, agotados, valor_inventario }
```

---

### 💳 VENTAS

#### Listar ventas
```
GET /api/sales
Parámetros:
  - user_id (uuid)
  - cash_register_id (uuid)
  - metodo_pago (string): efectivo|yape|plin
  - hoy (boolean)
  - este_mes (boolean)
  - fecha_inicio, fecha_fin (date)
  - per_page (integer)
```

#### Crear venta
```
POST /api/sales
Body:
{
  "cash_register_id": "uuid",
  "metodo_pago": "efectivo|yape|plin",
  "items": [
    {
      "product_id": "uuid",
      "cantidad": 2
    }
  ]
}
Requiere: Caja abierta del usuario autenticado
```

#### Obtener detalles de venta
```
GET /api/sales/{id}
```

#### Reporte de ventas
```
GET /api/sales/report
Parámetros:
  - fecha_inicio, fecha_fin (date)
Respuesta: { total_ventas, ingresos_totales, ganancia_total, por_metodo_pago }
```

---

### 🏧 CAJAS REGISTRADORAS

#### Listar cajas
```
GET /api/cash-registers
Parámetros:
  - abiertas_only (boolean)
  - cerradas_only (boolean)
  - user_id (uuid)
  - per_page (integer)
```

#### Abrir caja
```
POST /api/cash-registers/open
Body:
{
  "monto_apertura": 100.00
}
Nota: Solo una caja abierta por usuario
```

#### Obtener mi caja abierta
```
GET /api/cash-registers/my-open-register
```

#### Obtener caja
```
GET /api/cash-registers/{id}
```

#### Cerrar caja
```
POST /api/cash-registers/{id}/close
Body:
{
  "dinero_contado": 250.50,
  "observacion": "Diferencia de 5.50"
}
```

#### Resumen de caja
```
GET /api/cash-registers/{id}/summary
Respuesta: { monto_apertura, total_ventas, monto_esperado, diferencia, estado, etc }
```

---

### 📦 MOVIMIENTOS DE INVENTARIO

#### Listar movimientos
```
GET /api/inventory-movements
Parámetros:
  - product_id (uuid)
  - tipo (string): entrada|salida|ajuste
  - user_id (uuid)
  - fecha_inicio, fecha_fin (date)
  - per_page (integer)
```

#### Registrar movimiento
```
POST /api/inventory-movements
Body:
{
  "product_id": "uuid",
  "tipo": "entrada|salida|ajuste",
  "cantidad": 10,
  "motivo": "Descripción del movimiento"
}
Requiere role: admin
```

#### Obtener movimiento
```
GET /api/inventory-movements/{id}
```

#### Movimientos por producto
```
GET /api/inventory-movements/by-product/{productId}
```

#### Resumen de movimientos
```
GET /api/inventory-movements/summary
Parámetros:
  - fecha_inicio, fecha_fin (date)
Respuesta: { entradas, salidas, ajustes, total_movimientos }
```

---

### ⚠️ ALERTAS

#### Listar alertas
```
GET /api/alerts
Parámetros:
  - no_leidas_only (boolean)
  - criticas_only (boolean)
  - warnings_only (boolean)
  - tipo (string)
  - product_id (uuid)
  - nivel (string): info|warning|critical
  - per_page (integer)
```

#### Obtener alerta
```
GET /api/alerts/{id}
```

#### Marcar como leída
```
POST /api/alerts/{id}/mark-as-read
```

#### Marcar todas como leídas
```
POST /api/alerts/mark-all-as-read
```

#### Contar no leídas
```
GET /api/alerts/unread-count
Respuesta: { unread_count }
```

#### Resumen de alertas
```
GET /api/alerts/summary
Respuesta: { total_alertas, no_leidas, criticas, warnings, por_tipo }
```

#### Eliminar alertas leídas
```
DELETE /api/alerts/delete-read
```

---

### 👥 USUARIOS

#### Mi perfil
```
GET /api/users/me
```

#### Listar usuarios
```
GET /api/users
Parámetros:
  - role (string): admin|cajero
  - search (string)
  - per_page (integer)
```

#### Crear usuario
```
POST /api/users
Body:
{
  "name": "Juan",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "admin|cajero"
}
Requiere role: admin
```

#### Obtener usuario
```
GET /api/users/{id}
```

#### Actualizar usuario
```
PUT /api/users/{id}
Body:
{
  "name": "Nuevo nombre",
  "email": "nuevo@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
Nota: Solo admin puede cambiar roles
```

#### Eliminar usuario
```
DELETE /api/users/{id}
Requiere role: admin
```

#### Estadísticas
```
GET /api/users/stats
Requiere role: admin
Respuesta: { total_usuarios, admins, cajeros }
```

---

### 🧾 CLIENTES (Customers) - Sistema de Crédito

Endpoints para gestión de clientes de crédito. **No existe `limite_credito`** en la versión actual; el control es por `estado`.

#### Listar clientes
```
GET /api/customers
Parámetros:
  - estado (string): activo|inactivo|incobrable
  - con_deuda (boolean): si es true, filtra saldo_deuda > 0
  - morosos (boolean): si es true, clientes activos con deuda y última fecha de pago > 30 días
  - per_page (integer): Resultados por página (default: 20)
```
Nota: Este endpoint retorna el paginador de Laravel (incluye `data`, `links`, `meta`/`total` según versión).

#### Crear cliente
```
POST /api/customers
Body:
{
  "nombre": "Don Juan Pérez",
  "telefono": "999111222",
  "direccion": "Av. Principal 123"
}
```
Respuesta (201): `{ message, data }`
Nota: `estado` se asigna como `activo` por defecto.

#### Obtener cliente
```
GET /api/customers/{id}
```
Incluye relaciones del sistema de crédito (ventas a crédito, items, productos, pagos).

#### Actualizar cliente
```
PUT /api/customers/{id}
Body (campos opcionales):
{
  "nombre": "Nuevo Nombre",
  "telefono": "999000111",
  "direccion": "Nueva dirección",
  "estado": "activo|inactivo|incobrable"
}
```

#### Eliminar cliente
```
DELETE /api/customers/{id}
```
Nota: La ruta existe, pero requiere que el controller implemente `destroy()` para funcionar.

#### Historial de crédito del cliente
```
GET /api/customers/{id}/credit-history
Parámetros:
  - estado (string): filtra por estado de la venta a crédito
  - per_page (integer): Resultados por página (default: 20)
```

#### Resumen del cliente
```
GET /api/customers/{id}/summary
```
Respuesta (200):
`{ customer: {...}, resumen: { saldo_deuda, ventas_abiertas, ventas_pagadas, estado } }`

---

## Códigos de respuesta

- `200`: Éxito
- `201`: Creado
- `400`: Solicitud inválida
- `401`: No autenticado
- `403`: No autorizado
- `404`: No encontrado
- `422`: Validación fallida
- `500`: Error del servidor

## Estructura de respuestas

### Éxito
```json
{
  "data": { ... },
  "message": "Descripción"
}
```

### Error
```json
{
  "message": "Descripción del error",
  "errors": { "campo": ["Mensaje de validación"] }
}
```
