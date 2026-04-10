# 📋 Sistema de Ventas a Crédito - Guía de Implementación

## ✅ Lo que se ha implementado

### 1. **Migraciones Creadas** (4)
- `2026_04_06_153708_create_customers_table.php` - Tabla de clientes/deudores
- `2026_04_06_153709_create_credit_sales_table.php` - Ventas a crédito
- `2026_04_06_153710_create_credit_sale_items_table.php` - Detalles de items en venta a crédito
- `2026_04_06_153711_create_credit_payments_table.php` - Registro de pagos/abonos

### 2. **Modelos Implementados** (4)
- **Customer** - Cliente con deuda
  - `saldo_deuda`: Cuánto debe actualmente
  - `limite_credito`: Tope máximo de crédito
  - `estado`: activo | inactivo | incobrable
  - Métodos: `puedeComprarACredito()`, `creditoDisponible()`
  - Scopes: `activos()`, `conDeuda()`, `morosos()`

- **CreditSale** - Una venta a crédito
  - `total`: Monto total de la venta
  - `total_pagado`: Cuánto se ha abonado
  - `saldo_pendiente`: Lo que falta pagar
  - `estado`: abierta | parcial | pagada | incobrable
  - Método: `pagarAbono($monto)` - Procesa abonos
  - Scopes: `abiertas()`, `parciales()`, `pagadas()`, `pendientes()`

- **CreditSaleItem** - Cada producto en una venta a crédito
  - `cantidad`: Cantidad comprada
  - `precio_unitario`: Precio en el momento de la venta
  - `subtotal`: cantidad × precio
  - Factory method: `fromProduct($product, $cantidad)`

- **CreditPayment** - Cada pago/abono registrado
  - `monto`: Cantidad pagada
  - `metodo_pago`: efectivo | yape | plin | transferencia
  - `observacion`: Notas del pago
  - `created_at`: Fecha del pago

### 3. **Form Requests** (3)
- **StoreCreditSaleRequest** - Validaciones para crear venta a crédito
- **StoreCustomerRequest** - Validaciones para crear cliente
- **CreditPaymentRequest** - Validaciones para registrar pago

### 4. **Controllers** (2)
- **CustomerController**
  - `index()` - Listar clientes (con filtros por estado, con deuda, morosos)
  - `store()` - Crear cliente
  - `show()` - Ver detalles de cliente
  - `update()` - Actualizar cliente
  - `creditHistory()` - Ver historial de compras a crédito
  - `summary()` - Resumen de deuda y crédito disponible

- **CreditSaleController**
  - `index()` - Listar ventas a crédito
  - `store()` - Crear venta a crédito (con validación de stock y límite de crédito)
  - `show()` - Ver detalles de venta
  - `registerPayment()` - Registrar abono/pago
  - `payments()` - Historial de pagos

### 5. **Rutas Agregadas** (6 endpoints nuevos)
```php
// Clientes
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
GET    /api/customers/{id}/credit-history
GET    /api/customers/{id}/summary

// Ventas a Crédito
GET    /api/credit-sales
POST   /api/credit-sales
GET    /api/credit-sales/{id}
POST   /api/credit-sales/{id}/payment
GET    /api/credit-sales/{id}/payments
```

### 6. **Factories**
- **CustomerFactory** - Genera clientes de prueba con datos realistas
- **CreditSaleFactory** - Genera ventas a crédito con estados variables

### 7. **Tests** (12+ casos de prueba)
- Crear venta a crédito ✅
- Listar ventas a crédito ✅
- Ver detalles de venta ✅
- Filtrar clientes por estado ✅
- Registrar pago parcial ✅
- Completar pago ✅
- Ver historial de crédito ✅
- Más...

---

## 🔄 **Flujo Completo de un Cliente Deudor**

### Día 1: Compra a Crédito
```
POST /api/customers
{
  "nombre": "Don Juan Pérez",
  "telefono": "999111222",
  "direccion": "Av. Principal 123",
  "limite_credito": 100000
}

Response: 
{
  "id": "uuid",
  "nombre": "Don Juan Pérez",
  "saldo_deuda": 0,
  "limite_credito": 100000,
  "estado": "activo"
}
```

### Día 1: Venta a Crédito
```
POST /api/credit-sales
{
  "customer_id": "uuid-don-juan",
  "items": [
    {
      "product_id": "uuid-arroz",
      "cantidad": 5           // 5 kg
    },
    {
      "product_id": "uuid-refresco",
      "cantidad": 2
    }
  ]
}

Response:
{
  "id": "sale-1",
  "total": 50000,             // $50,000
  "total_pagado": 0,
  "saldo_pendiente": 50000,
  "estado": "abierta"
}

Resultado:
✅ Stock de arroz: 5 kg menos
✅ Stock de refresco: 2 unidades menos
✅ Deuda de Don Juan: $50,000
❌ Caja: No entra dinero
```

### Día 4: Abono Parcial
```
POST /api/credit-sales/{sale-1}/payment
{
  "monto": 20000,
  "metodo_pago": "efectivo",
  "observacion": "Primer abono"
}

Response:
{
  "saldo_pendiente": 30000,
  "estado": "parcial"
}

Resultado:
✅ Deuda de Don Juan: $30,000
✅ Caja: +$20,000
❌ Stock: Sin cambios (ya estaba decrementado)
```

### Día 10: Segunda Compra a Crédito
```
POST /api/credit-sales
{
  "customer_id": "uuid-don-juan",
  "items": [
    {
      "product_id": "uuid-maiz",
      "cantidad": 1
    }
  ]
}

Resultado:
✅ Stock de maíz: 1 kg menos
✅ Deuda total: $30,000 + $8,000 = $38,000 (ACUMULADA)
```

### Día 20: Pago Final
```
POST /api/credit-sales/{sale-2}/payment
{
  "monto": 38000,
  "metodo_pago": "transferencia"
}

Resultado:
✅ Deuda de Don Juan: $0
✅ Estado de ventas: "pagada"
✅ Caja: +$38,000
❌ Stock: No cambia (nunca se devolvió)
```

---

## 📊 **Queries Importantes**

### Ver clientes morosos (>30 días sin pagar)
```bash
GET /api/customers?morosos=1
```

### Ver clientes con deuda pendiente
```bash
GET /api/customers?con_deuda=1
```

### Ver ventas a crédito abiertas
```bash
GET /api/credit-sales?estado=abierta
```

### Ver historial de pagos de un cliente
```bash
GET /api/customers/{id}/credit-history
```

### Ver resumen de deuda
```bash
GET /api/customers/{id}/summary
```

Response:
```json
{
  "resumen": {
    "saldo_deuda": 30000,
    "limite_credito": 100000,
    "credito_disponible": 70000,
    "ventas_abiertas": 1,
    "ventas_pagadas": 2,
    "estado": "activo"
  }
}
```

---

## 🎯 **Regla Clave: Stock SIEMPRE se decrementa**

| Tipo de Venta | Stock Cambia | Dinero Entra | Resolución |
|---------------|-------------|-------------|-----------|
| **POS Normal** (efectivo) | ✅ Baja | ✅ Al instante | Cerrada ✅ |
| **A Crédito** (apuntación) | ✅ Baja | ❌ No | Abierta 🔴 |
| **Abono parcial** | ❌ No | ✅ Entra | Sigue abierta |
| **Abono final** | ❌ No | ✅ Entra | Se cierra ✅ |
| **Devolución** | ✅ Sube | ✅/❌ Depende | Reduce deuda |

---

## 🔐 **Validaciones Implementadas**

1. **Stock**: No se vende sin stock suficiente
2. **Límite de crédito**: Don Juan no puede superar su límite
3. **Pago**: No puede abonar más que el saldo pendiente
4. **Cliente**: Debe existir para crear venta a crédito
5. **Producto**: Debe existir en el catálogo
6. **Métodos de pago**: Solo: efectivo, yape, plin, transferencia

---

## 🚀 **Próximos Pasos Sugeridos**

1. **Reportes de Cobranzas**: Quiénes deben y desde cuándo
2. **Alertas automáticas**: Notificar por morosos
3. **Devoluciones de productos**: Reversar venta a crédito
4. **Descuentos por pronto pago**: Incentivar cobro rápido
5. **Integración con SMS**: Recordatorios de pago
6. **Cierre de mes**: Reportes por período
7. **Múltiples vendedores**: Trackear por cajero
8. **Historial de cambios**: Auditoría completa

---

## 📝 **Notas sobre la Implementación**

- ✅ UUIDs para todos los IDs (consistente con el proyecto)
- ✅ Transacciones atómicas en creación de ventas
- ✅ Índices de BD para reportes rápidos
- ✅ Validaciones en Form Requests
- ✅ Soft deletes NO usados (datos son importantes)
- ✅ Tests con Pest (12+ casos)
- ✅ Enum para estados
- ✅ Casteos para decimales (precisión monetaria)

---

## 🧪 **Correr los Tests**

```bash
php artisan test tests/Feature/CustomerTest.php --no-coverage
php artisan test tests/Feature/CreditSaleTest.php --no-coverage
php artisan test tests/Feature/ --no-coverage
```

---

**Sistema de Ventas a Crédito ✅ COMPLETAMENTE IMPLEMENTADO**

Don Juan ahora puede ir a tu tienda, llevar 5 kg de arroz, y decir:
*"Apúntamelo a mi cuenta"* 

Y tu sistema sabrá exactamente:
- ✅ Qué llevó
- ✅ Cuánto debe
- ✅ Cuándo lo compró
- ✅ Cuánto ha pagado
- ✅ Qué le falta pagar

¡Perfecto para bodega o colegio! 🎯
