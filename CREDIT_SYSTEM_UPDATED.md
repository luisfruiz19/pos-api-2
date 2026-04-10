# 📝 Sistema de Crédito - Actualización v2

## ✅ CAMBIO IMPORTANTE: Eliminación de Límite de Crédito

A partir de esta versión, **se eliminó completamente el concepto de `limite_credito`**. 

### 🔄 Lo que cambió:

**ANTES:**
```php
// Cliente no podía comprar más que su límite de crédito
if ($saldo_deuda + $monto_compra > $limite_credito) {
    return error "Límite de crédito excedido";
}
```

**AHORA:**
```php
// Cliente puede comprar CUALQUIER cantidad
// Solo se valida que esté en estado 'activo'
if ($customer->estado !== 'activo') {
    return error "Cliente inactivo";
}
// ✅ Sale creada sin restricción de monto
```

---

## 📊 Cambios en las Tablas

### Campo `limite_credito` eliminado de:
- ✅ `customers` - Migración actualizada
- ✅ `Customer::factory()` - Factory actualizada
- ✅ Endpoints `/api/customers` - Ya no devuelve `limite_credito`

### Resumen del cliente - Simplificado:

**Antes:**
```json
{
  "saldo_deuda": 50000,
  "limite_credito": 100000,
  "credito_disponible": 50000,
  "ventas_abiertas": 2,
  "ventas_pagadas": 5,
  "estado": "activo"
}
```

**Ahora:**
```json
{
  "saldo_deuda": 50000,
  "ventas_abiertas": 2,
  "ventas_pagadas": 5,
  "estado": "activo"
}
```

---

## 🎯 Validaciones por Estado

**El cliente PUEDE comprar si:**
- ✓ `estado = 'activo'`
- ✓ Puede deber cualquier monto
- ✓ No hay restricción de límite

**El cliente NO PUEDE comprar si:**
- ✗ `estado = 'inactivo'`
- ✗ `estado = 'incobrable'`

---

## 🚀 Endpoints - Sin cambios en la funcionalidad

Todos los endpoints siguen funcionando igual, pero con las respuestas simplificadas:

### GET `/api/customers/{id}/summary`
```bash
curl http://localhost:8000/api/customers/UUID/summary
```

**Response:**
```json
{
  "customer": {
    "id": "550e8400...",
    "nombre": "Don Juan",
    "saldo_deuda": "50000.00",
    "estado": "activo"
  },
  "resumen": {
    "saldo_deuda": 50000,
    "ventas_abiertas": 2,
    "ventas_pagadas": 5,
    "estado": "activo"
  }
}
```

---

## 💾 Migración de BD

Si tienes BD existente con `limite_credito`:

```bash
# Opción 1: Reset completo (desarrollo)
php artisan migrate:fresh --seed

# Opción 2: Crear migración de rollback (producción)
# Crear archivo database/migrations/YYYY_XX_XX_xxxxxx_remove_limite_credito.php
# Con Schema::table('customers', function(Blueprint $table) {
#     $table->dropColumn('limite_credito');
# });
```

---

## 📋 Flujo Completo Actualizado

### Escenario: Don Juan compra sin límite

```
1. Don Juan (activo) viene a comprar
   ╔════════════════════════╗
   ║ Crear venta a crédito  ║ → POST /api/credit-sales
   ║ Items: cualquier monto ║
   ╚════════════════════════╝
   ✅ PERMITIDO (sin restricción)

2. Venta creada
   ┌─────────────────────────────┐
   │ total: 250,000              │
   │ saldo_pendiente: 250,000    │ ← Don Juan debe 250,000
   │ estado: abierta             │
   └─────────────────────────────┘

3. Don Juan paga en cuotas
   • Día 3: paga 100,000 → estado: parcial
   • Día 7: paga 100,000 → estado: parcial
   • Día 10: paga 50,000 → estado: pagada ✅

4. La deuda de Don Juan se actualiza
   • Inicial: 250,000
   • Después de cuota 1: 150,000
   • Después de cuota 2: 50,000
   • Después de cuota 3: 0 ✅
```

---

## 🔍 Verificar Cambios

```bash
# Ver clientes con deuda (sin límite)
GET /api/customers?con_deuda=1

# Ver resumen (sin limite_credito)
GET /api/customers/{id}/summary

# Crear venta con cualquier monto
POST /api/credit-sales
{
  "customer_id": "UUID",
  "items": [
    {"product_id": "UUID", "cantidad": 100}  # ← Sin restricción
  ]
}
```

---

## ✨ Beneficios

✅ **Simplicidad:** Una restricción menos  
✅ **Flexibilidad:** Bodeguero decide cuándo aceptar/rechazar  
✅ **Control:** Usa estado (activo/inactivo) para permitir/negar  
✅ **Escalabilidad:** Fácil agregar límites personalizados después  

---

## 🧪 Tests 

Todos los tests pasados (15/15 ✓):

```bash
php artisan test tests/Feature/CreditSalesEndToEndTest.php --no-coverage
```

### Tests que validan:
- ✅ Cliente ACTIVO puede comprar cualquier monto
- ✅ Cliente INACTIVO no puede comprar
- ✅ Stock se valida (sin límite de crédito)
- ✅ Pagos parciales funcionan
- ✅ Deuda se acumula correctamente

---

## 📌 Próximas Mejoras Opcionales

Si quieres volver a agregar límites:

```php
// Opción: Campo límite_credito personalizado por cliente
// Opción: Límite global en config
// Opción: Límites por rango de cliente (VIP, Regular, etc)
// Opción: Alertas si deuda > X threshold
```

---

**Version:** 2.0  
**Fecha:** 2026-04-06  
**Estado:** ✅ Production Ready
