# 📋 Changelog - Sistema de Crédito

## v2.0 - 2026-04-06

### 🔄 Breaking Changes

#### ❌ Eliminado: `limite_credito`

**Razón:** Después de usar el sistema en bodega/escuela, se determinó que **los límites de crédito los maneja el comerciante** (decisión de negocio), no una regla técnica.

**Cambio:**
- Campo `limite_credito` eliminado de tabla `customers`
- Validación de límite de crédito eliminada de `store()` en `CreditSaleController`
- Método `creditoDisponible()` eliminado de modelo `Customer`
- Factory de `Customer` actualizada (sin `limite_credito`)

**Comportamiento anterior:**
```
Si: saldo_deuda + monto_compra > limite_credito
Entonces: RECHAZAR compra (422)
```

**Comportamiento nuevo:**
```
Si: customer->estado === 'activo'
Entonces: PERMITIR compra (sin restricción de monto)
Si: customer->estado !== 'activo'
Entonces: RECHAZAR compra (422)
```

---

### ✨ Nuevas características

**1. Compras sin límite numérico**
```php
// Don Juan (activo) puede comprar $0.01 o $1,000,000
POST /api/credit-sales {
  customer_id: "UUID",
  items: [...]
}
// ✅ Respuesta 201 - Creada
```

**2. Control via estado**
```php
// Para bloquear a cliente: inactivo o incobrable
PUT /api/customers/{id} {
  "estado": "inactivo"
}
// Ahora ese cliente NO puede comprar
```

---

### 📊 Cambios en Base de Datos

**Migración ejecutada:**
```sql
ALTER TABLE customers DROP COLUMN limite_credito;
```

**Antes:**
```
id | nombre | saldo_deuda | limite_credito | estado
UUID | Don Juan | 50000 | 100000 | activo
```

**Después:**
```
id | nombre | saldo_deuda | estado
UUID | Don Juan | 50000 | activo
```

---

### 🔧 Cambios en API Endpoints

#### GET `/api/customers` (Sin cambios, sigue igual)
```json
{
  "current_page": 1,
  "per_page": 20,
  "total": 3,
  "data": [...]
}
```

#### GET `/api/customers/{id}` (Sin cambios)
```json
{
  "data": {
    "nombre": "Don Juan",
    "saldo_deuda": 50000,
    "estado": "activo"
    // ❌ Antes: "limite_credito": 100000,
  }
}
```

#### GET `/api/customers/{id}/summary` (SIMPLIFICADO)

**Antes:**
```json
{
  "resumen": {
    "saldo_deuda": 50000,
    "limite_credito": 100000,          ❌ REMOVIDO
    "credito_disponible": 50000,       ❌ REMOVIDO
    "ventas_abiertas": 2,
    "ventas_pagadas": 5,
    "estado": "activo"
  }
}
```

**Ahora:**
```json
{
  "resumen": {
    "saldo_deuda": 50000,
    "ventas_abiertas": 2,
    "ventas_pagadas": 5,
    "estado": "activo"
  }
}
```

---

### 🧪 Testing

**15/15 tests PASSING** ✅

Nuevos tests agregados:
- `it('allows customer to buy any amount when active')` ✅
- `it('marks customer as inactive prevents credit purchases')` ✅
- `it('accumulates debt correctly across multiple sales')` ✅

Removed test:
- `it('prevents customer from exceeding credit limit')` ❌

---

### 📝 Cambios en Código

**Files Modified:**
- `/database/migrations/2026_04_06_153708_create_customers_table.php`
- `/app/Models/Customer.php`
- `/app/Http/Controllers/API/CustomerController.php`
- `/app/Http/Controllers/API/CreditSaleController.php`
- `/app/Http/Requests/StoreCustomerRequest.php`
- `/database/factories/CustomerFactory.php`
- `/bootstrap/app.php` (mejorado exception handling)
- `/tests/Feature/CreditSalesEndToEndTest.php` (15 tests)

**Files Created:**
- `/CREDIT_SYSTEM_UPDATED.md` - Documentación
- `/CHANGELOG_CREDIT_SYSTEM.md` - Cambios

---

### 💡 Ejemplo: Antes vs Después

**ANTES (v1.0):**
```http
POST /api/customers
{
  "nombre": "Bodega ABC",
  "limite_credito": 50000  ← Obligatorio
}

POST /api/credit-sales
{
  "customer_id": "UUID",
  "items": [{"product_id": "UUID", "cantidad": 10}]  // total: 60,000
}

// ❌ RECHAZADO: "Límite de crédito excedido. Disponible: 50,000"
```

**AHORA (v2.0):**
```http
POST /api/customers
{
  "nombre": "Bodega ABC"
  // ❌ No pedir limite_credito
}

POST /api/credit-sales
{
  "customer_id": "UUID",
  "items": [{"product_id": "UUID", "cantidad": 10}]  // total: 60,000
}

// ✅ ACEPTADO: Venta creada en estado 'abierta'
```

---

### 🔒 Control Alternativo

Si necesitas control, usar el estado del cliente:

```php
// 1. Cliente activo = SÍ compra
customer->estado = 'activo';  // ✅ Permite compras

// 2. Cliente temporalmente bloqueado
customer->estado = 'inactivo';  // ❌ Rechaza compras

// 3. Cliente que no pagará
customer->estado = 'incobrable';  // ❌ Rechaza compras
```

---

### 📌 Pasos de Migración

**Si tienes BD con datos:**

```bash
# 1. Backup de clientes
$ php artisan tinker
> \App\Models\Customer::get()->each(function($c) {
    echo "$c->nombre: $c->saldo_deuda\n";
  });

# 2. Ejecutar migrate fresh (desarrollo)
$ php artisan migrate:fresh --seed

# 3. O crear migración de eliminación (producción)
$ php artisan make:migration remove_limite_credito_from_customers_table
# Editar e incluir: $table->dropColumn('limite_credito');
$ php artisan migrate
```

---

### ⚠️ Issues Resueltos

1. **Excepción 500 en validaciones:**
   - ✅ Agregado custom exception handler en `bootstrap/app.php`
   - ✅ `DomainException` ahora retorna 422

2. **Estructura inconsistente de responses:**
   - ✅ Todos los endpoints ahora envuelven data: `{ data: {...} }`
   - ✅ Paginación usa clave `total` no `meta.total`

3. **Validación de métodos de pago:**
   - ✅ `failedValidation` personalizada en `CreditPaymentRequest`

---

### 📚 Documentación Actualizada

- ✅ `CREDIT_SYSTEM.md` - Mantener para referencia histórica
- ✅ `CREDIT_SYSTEM_UPDATED.md` - Nueva documentación v2
- ✅ `CREDIT_EXAMPLES.md` - Sin cambios en ejemplos de uso
- ✅ `CREDIT_TROUBLESHOOTING.md` - Actualizar si encontras issues

---

### 🧹 Limpieza

**Métodos removidos:**
```php
// ❌ Ya no existen:
Customer::creditoDisponible()
Customer::puedeComprarACredito($monto)  // ← Ahora solo chequea estado
```

**Campos de BD removidos:**
```
customers.limite_credito
```

---

### ✅ Validación Final

```bash
# Ejecutar todos los tests
$ php artisan test tests/Feature/CreditSalesEndToEndTest.php --no-coverage

# Result: 15 PASSED ✓

# Verificar no hay warnings
$ php artisan migrate --dry-run
```

---

**Status:** ✅ READY FOR PRODUCTION  
**Tested:** 15/15 tests passing  
**Date:** 2026-04-06  
**Author:** Development Team
