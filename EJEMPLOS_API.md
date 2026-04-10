# Ejemplos de Uso de la API

## 🔐 Autenticación

Primero necesitas un token. Por ahora puedes generar uno manualmente en la BD o crear un endpoint de login.

```bash
# Ejemplo con token
TOKEN="tu_token_aqui"
```

---

## 1️⃣ PRODUCTOS

### Crear producto
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Laptop",
    "precio_compra": 800,
    "precio_venta": 1200,
    "stock": 5,
    "stock_minimo": 2,
    "codigo_barras": "LAP-001"
  }'
```

### Listar productos
```bash
curl -X GET "http://localhost:8000/api/products?search=Laptop&per_page=20" \
  -H "Authorization: Bearer $TOKEN"
```

### Productos con stock bajo
```bash
curl -X GET http://localhost:8000/api/products/low-stock \
  -H "Authorization: Bearer $TOKEN"
```

### Estadísticas
```bash
curl -X GET http://localhost:8000/api/products/stats \
  -H "Authorization: Bearer $TOKEN"
```

---

## 2️⃣ CAJAS REGISTRADORAS

### Abrir caja
```bash
curl -X POST http://localhost:8000/api/cash-registers/open \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "monto_apertura": 200.00
  }'

# Respuesta:
# {
#   "message": "Caja abierta exitosamente",
#   "data": {
#     "id": "uuid",
#     "user_id": "uuid",
#     "monto_apertura": "200.00",
#     "estado": "abierta",
#     ...
#   }
# }
```

### Ver mi caja abierta
```bash
curl -X GET http://localhost:8000/api/cash-registers/my-open-register \
  -H "Authorization: Bearer $TOKEN"
```

---

## 3️⃣ VENTAS

### Crear venta
```bash
curl -X POST http://localhost:8000/api/sales \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "cash_register_id": "uuid-de-la-caja",
    "metodo_pago": "efectivo",
    "items": [
      {
        "product_id": "uuid-producto-1",
        "cantidad": 2
      },
      {
        "product_id": "uuid-producto-2",
        "cantidad": 1
      }
    ]
  }'

# La API automáticamente:
# - Valida que la caja esté abierta
# - Verifica stock de cada producto
# - Crea la venta y detalles
# - Decrementa stock
# - Genera alertas de stock bajo/agotado
# - Acumula el total en la caja
```

### Listar ventas del día
```bash
curl -X GET "http://localhost:8000/api/sales?hoy=true" \
  -H "Authorization: Bearer $TOKEN"
```

### Reporte de ventas
```bash
curl -X GET "http://localhost:8000/api/sales/report?fecha_inicio=2026-03-01&fecha_fin=2026-03-31" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 4️⃣ CERRAR CAJA

```bash
curl -X POST http://localhost:8000/api/cash-registers/uuid-de-la-caja/close \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "dinero_contado": 545.50,
    "observacion": "Diferencia de 5.50"
  }'

# Respuesta:
# {
#   "message": "Caja cerrada exitosamente",
#   "data": {
#     "monto_esperado": 550.00,
#     "dinero_contado": 545.50,
#     "diferencia": -5.00,
#     "estado": "cerrada"
#   }
# }
```

---

## 5️⃣ MOVIMIENTOS DE INVENTARIO

### Registrar entrada de stock
```bash
curl -X POST http://localhost:8000/api/inventory-movements \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "uuid-producto",
    "tipo": "entrada",
    "cantidad": 50,
    "motivo": "Compra a proveedor"
  }'
```

### Registrar ajuste de stock
```bash
curl -X POST http://localhost:8000/api/inventory-movements \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "uuid-producto",
    "tipo": "ajuste",
    "cantidad": -2,
    "motivo": "Robo de mercancía"
  }'
```

### Historial de movimientos
```bash
curl -X GET "http://localhost:8000/api/inventory-movements?product_id=uuid-producto" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 6️⃣ ALERTAS

### Ver alertas no leídas
```bash
curl -X GET "http://localhost:8000/api/alerts?no_leidas_only=true" \
  -H "Authorization: Bearer $TOKEN"
```

### Ver alertas críticas
```bash
curl -X GET "http://localhost:8000/api/alerts?criticas_only=true" \
  -H "Authorization: Bearer $TOKEN"
```

### Marcar alerta como leída
```bash
curl -X POST http://localhost:8000/api/alerts/uuid-alerta/mark-as-read \
  -H "Authorization: Bearer $TOKEN"
```

### Resumen de alertas
```bash
curl -X GET http://localhost:8000/api/alerts/summary \
  -H "Authorization: Bearer $TOKEN"

# Respuesta:
# {
#   "total_alertas": 15,
#   "no_leidas": 3,
#   "criticas": 1,
#   "warnings": 5,
#   "por_tipo": [
#     {"tipo": "stock_bajo", "cantidad": 5},
#     {"tipo": "stock_agotado", "cantidad": 1}
#   ]
# }
```

---

## 7️⃣ USUARIOS

### Crear usuario
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "Segura123!",
    "password_confirmation": "Segura123!",
    "role": "cajero"
  }'

# Nota: Solo admins pueden crear usuarios
```

### Mi perfil
```bash
curl -X GET http://localhost:8000/api/users/me \
  -H "Authorization: Bearer $TOKEN"
```

### Estadísticas de usuarios
```bash
curl -X GET http://localhost:8000/api/users/stats \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📊 Script Completo de Ejemplo

```bash
#!/bin/bash

TOKEN="tu_token_aqui"
BASE_URL="http://localhost:8000/api"

echo "1. Abrir caja..."
CAJA=$(curl -s -X POST $BASE_URL/cash-registers/open \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"monto_apertura": 1000}' | jq -r '.data.id')

echo "Caja abierta: $CAJA"

echo "2. Ver alertas..."
curl -s -X GET "$BASE_URL/alerts/summary" \
  -H "Authorization: Bearer $TOKEN" | jq

echo "3. Crear venta..."
curl -s -X POST $BASE_URL/sales \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"cash_register_id\": \"$CAJA\",
    \"metodo_pago\": \"efectivo\",
    \"items\": [
      {
        \"product_id\": \"uuid-producto-1\",
        \"cantidad\": 2
      }
    ]
  }" | jq

echo "4. Cerrar caja..."
curl -s -X POST $BASE_URL/cash-registers/$CAJA/close \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "dinero_contado": 1100,
    "observacion": "Ganancia positiva"
  }' | jq
```

---

## 🧪 Pruebas con Postman/Insomnia

Importa esta colección en Postman:

```json
{
  "info": {
    "name": "POS API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Get Token",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/auth/login"
          }
        }
      ]
    },
    {
      "name": "Products",
      "item": [
        {
          "name": "List",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/products"
          }
        },
        {
          "name": "Create",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/products"
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api"
    },
    {
      "key": "token",
      "value": ""
    }
  ]
}
```
