# 🛍️ Ejemplos de Uso - Sistema de Ventas a Crédito

## 1️⃣ CREAR UN CLIENTE

### Request
```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Don Juan Pérez",
    "telefono": "999111222",
    "direccion": "Av. Principal 123, apt. 4B",
    "limite_credito": 100000
  }'
```

### Response
```json
{
  "message": "Cliente creado exitosamente",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "nombre": "Don Juan Pérez",
    "telefono": "999111222",
    "direccion": "Av. Principal 123, apt. 4B",
    "saldo_deuda": 0,
    "limite_credito": 100000,
    "estado": "activo",
    "ultima_compra_at": null,
    "ultima_pago_at": null,
    "created_at": "2026-04-06T15:00:00.000000Z",
    "updated_at": "2026-04-06T15:00:00.000000Z"
  }
}
```

---

## 2️⃣ CREAR UNA VENTA A CRÉDITO

### Request
```bash
curl -X POST http://localhost:8000/api/credit-sales \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": "550e8400-e29b-41d4-a716-446655440000",
    "items": [
      {
        "product_id": "123e4567-e89b-12d3-a456-426614174000",
        "cantidad": 5
      },
      {
        "product_id": "223e4567-e89b-12d3-a456-426614174000",
        "cantidad": 2
      }
    ]
  }'
```

### Response
```json
{
  "message": "Venta a crédito registrada exitosamente",
  "data": {
    "id": "660e8400-e29b-41d4-a716-446655440111",
    "customer_id": "550e8400-e29b-41d4-a716-446655440000",
    "total": 50000,
    "total_pagado": 0,
    "saldo_pendiente": 50000,
    "estado": "abierta",
    "created_at": "2026-04-06T15:30:00.000000Z",
    "updated_at": "2026-04-06T15:30:00.000000Z",
    "customer": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "nombre": "Don Juan Pérez",
      "saldo_deuda": 50000
    },
    "items": [
      {
        "id": "770e8400-e29b-41d4-a716-446655440222",
        "credit_sale_id": "660e8400-e29b-41d4-a716-446655440111",
        "product_id": "123e4567-e89b-12d3-a456-426614174000",
        "cantidad": 5,
        "precio_unitario": 5000,
        "subtotal": 25000,
        "product": {
          "nombre": "Arroz Premium",
          "precio_venta": 5000
        }
      },
      {
        "id": "880e8400-e29b-41d4-a716-446655440333",
        "product_id": "223e4567-e89b-12d3-a456-426614174000",
        "cantidad": 2,
        "precio_unitario": 12500,
        "subtotal": 25000,
        "product": {
          "nombre": "Refresco Coca",
          "precio_venta": 12500
        }
      }
    ]
  }
}
```

✅ **Resultado:**
- Stock de "Arroz" decrementado en 5
- Stock de "Refresco" decrementado en 2
- Deuda de Don Juan: $50,000
- Caja: No entra dinero

---

## 3️⃣ REGISTRAR UN ABONO PARCIAL

### Request (3 días después)
```bash
curl -X POST http://localhost:8000/api/credit-sales/660e8400-e29b-41d4-a716-446655440111/payment \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "monto": 20000,
    "metodo_pago": "efectivo",
    "observacion": "Primer abono de Don Juan"
  }'
```

### Response
```json
{
  "message": "Pago registrado correctamente",
  "data": {
    "monto_pagado": 20000,
    "saldo_pendiente": 30000,
    "estado": "parcial"
  }
}
```

✅ **Resultado:**
- Deuda de Don Juan: $50,000 - $20,000 = $30,000
- Caja: +$20,000
- Stock: Sin cambios
- Estado de venta: parcial

---

## 4️⃣ VER RESUMEN DE CLIENTE

### Request
```bash
curl -X GET http://localhost:8000/api/customers/550e8400-e29b-41d4-a716-446655440000/summary \
  -H "Authorization: Bearer $TOKEN"
```

### Response
```json
{
  "customer": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "nombre": "Don Juan Pérez",
    "telefono": "999111222",
    "saldo_deuda": 30000,
    "limite_credito": 100000,
    "estado": "activo"
  },
  "resumen": {
    "saldo_deuda": 30000,
    "limite_credito": 100000,
    "credito_disponible": 70000,
    "ventas_abiertas": 1,
    "ventas_pagadas": 0,
    "estado": "activo"
  }
}
```

---

## 5️⃣ LISTAR CLIENTES CON DEUDA

### Request
```bash
curl -X GET "http://localhost:8000/api/customers?con_deuda=1&per_page=20" \
  -H "Authorization: Bearer $TOKEN"
```

### Response
```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "nombre": "Don Juan Pérez",
      "telefono": "999111222",
      "saldo_deuda": 30000,
      "limite_credito": 100000,
      "estado": "activo",
      "ultima_compra_at": "2026-04-06T15:30:00.000000Z",
      "ultima_pago_at": "2026-04-06T18:00:00.000000Z"
    },
    {
      "id": "660e8400-e29b-41d4-a716-446655440222",
      "nombre": "Doña María García",
      "saldo_deuda": 15000,
      "limite_credito": 50000,
      "estado": "activo"
    }
  ],
  "meta": {
    "total": 2,
    "per_page": 20,
    "current_page": 1
  }
}
```

---

## 6️⃣ VER HISTORIAL DE CRÉDITO DE UN CLIENTE

### Request
```bash
curl -X GET "http://localhost:8000/api/customers/550e8400-e29b-41d4-a716-446655440000/credit-history?per_page=10" \
  -H "Authorization: Bearer $TOKEN"
```

### Response
```json
{
  "data": [
    {
      "id": "660e8400-e29b-41d4-a716-446655440111",
      "customer_id": "550e8400-e29b-41d4-a716-446655440000",
      "total": 50000,
      "total_pagado": 20000,
      "saldo_pendiente": 30000,
      "estado": "parcial",
      "created_at": "2026-04-06T15:30:00.000000Z",
      "items": [
        {
          "cantidad": 5,
          "precio_unitario": 5000,
          "subtotal": 25000,
          "product": {
            "nombre": "Arroz Premium"
          }
        },
        {
          "cantidad": 2,
          "precio_unitario": 12500,
          "subtotal": 25000,
          "product": {
            "nombre": "Refresco Coca"
          }
        }
      ],
      "payments": [
        {
          "id": "770e8400-e29b-41d4-a716-446655440444",
          "monto": 20000,
          "metodo_pago": "efectivo",
          "observacion": "Primer abono de Don Juan",
          "created_at": "2026-04-09T18:00:00.000000Z"
        }
      ]
    }
  ],
  "meta": {
    "total": 1,
    "current_page": 1
  }
}
```

---

## 7️⃣ VER HISTORIAL DE PAGOS

### Request
```bash
curl -X GET http://localhost:8000/api/credit-sales/660e8400-e29b-41d4-a716-446655440111/payments \
  -H "Authorization: Bearer $TOKEN"
```

### Response
```json
{
  "credit_sale_id": "660e8400-e29b-41d4-a716-446655440111",
  "total_venta": 50000,
  "total_pagado": 20000,
  "saldo_pendiente": 30000,
  "pagos": [
    {
      "id": "770e8400-e29b-41d4-a716-446655440444",
      "credit_sale_id": "660e8400-e29b-41d4-a716-446655440111",
      "monto": 20000,
      "metodo_pago": "efectivo",
      "observacion": "Primer abono de Don Juan",
      "created_at": "2026-04-09T18:00:00.000000Z"
    }
  ]
}
```

---

## 8️⃣ HACER PAGO FINAL

### Request
```bash
curl -X POST http://localhost:8000/api/credit-sales/660e8400-e29b-41d4-a716-446655440111/payment \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "monto": 30000,
    "metodo_pago": "transferencia",
    "observacion": "Pago final"
  }'
```

### Response
```json
{
  "message": "Pago registrado correctamente",
  "data": {
    "monto_pagado": 30000,
    "saldo_pendiente": 0,
    "estado": "pagada"
  }
}
```

✅ **Resultado:**
- Deuda de Don Juan: $0
- Venta CERRADA ✅
- Caja: +$30,000 más
- Total caja por esta venta: $50,000

---

## 🔍 FILTROS ÚTILES

### Listar clientes morosos (>30 días sin pagar)
```bash
GET /api/customers?morosos=1
```

### Listar clientes por estado
```bash
GET /api/customers?estado=activo
GET /api/customers?estado=inactivo
GET /api/customers?estado=incobrable
```

### Listar ventas abiertas
```bash
GET /api/credit-sales?estado=abierta
```

### Listar ventas pagadas
```bash
GET /api/credit-sales?estado=pagada
```

### Filtrar por cliente específico
```bash
GET /api/credit-sales?customer_id=UUID
```

---

## ❌ ERRORES COMUNES

### 1. Cuando el cliente no existe
```json
{
  "message": "No query results found for model [App\\Models\\Customer]",
  "status": 404
}
```

### 2. Cuando se excede el límite de crédito
```json
{
  "message": "Límite de crédito excedido. Disponible: 50000, Solicitado: 60000",
  "status": 422
}
```

### 3. Cuando no hay stock
```json
{
  "message": "Stock insuficiente para Arroz Premium. Disponible: 2, Solicitado: 5",
  "status": 422
}
```

### 4. Cuando intentas pagar más que el saldo
```json
{
  "message": "El monto excede el saldo pendiente de 30000",
  "status": 422
}
```

---

## 📊 REPORTES ÚTILES

### Clientes que deben más dinero
```bash
GET /api/customers?con_deuda=1&per_page=100
```

### Ventas a crédito pendientes por cobrar
```bash
GET /api/credit-sales?pendientes=1
```

### Ver detalles completos de un cliente
```bash
GET /api/customers/{id}
```

---

## 💡 TIPS PRÁCTICOS

1. **Guardar el ID del cliente**: Para reutilizar cuando vuelva a comprar
2. **Usar notas en pagos**: Documentar observaciones importantes
3. **Revisar crédito disponible**: Antes de aceptar una compra a crédito
4. **Hacer reportes mensuales**: Ver quiénes deben y desde cuándo
5. **Recordatorios**: Contactar morosos después de 30 días

---

**¡Sistema listo para usar! 🎯**
