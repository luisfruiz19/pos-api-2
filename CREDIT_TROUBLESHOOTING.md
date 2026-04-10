# 🔧 Troubleshooting - Sistema de Crédito

## 📋 Tabla de Problemas y Soluciones

| Problema | Causa | Solución |
|----------|-------|----------|
| "No query results found" al pagar | Venta/cliente no existe | Verifica el UUID en BD: `SELECT * FROM credit_sales WHERE id = 'UUID'` |
| "Límite de crédito excedido" | Cliente no tiene suficiente crédito | Ve a `GET /api/customers/{id}/summary` para ver `credito_disponible` |
| "Stock insuficiente" | Producto no tiene suficientes unidades | Aumenta stock en productos: `UPDATE products SET stock = stock + 100 WHERE id = 'UUID'` |
| "El monto excede el saldo pendiente" | Intentas pagar más de lo que debe | El `saldo_pendiente` debe ser mayor al `monto` del pago |
| Deuda no se actualiza después del pago | Pago registrado pero cliente aún debe | Verifica que `CreditPayment` se creó: `SELECT * FROM credit_payments WHERE credit_sale_id = 'UUID'` |
| Cliente bloqueado para comprar | Estado es "inactivo" o "incobrable" | Cambiar estado: `UPDATE customers SET estado = 'activo' WHERE id = 'UUID'` |

---

## 🐛 Problemas Comunes

### Problema 1: "Deuda del cliente no coincide con suma de ventas"

**Síntomas:**
```
Customer.saldo_deuda = 100,000
Pero sum(credit_sales.saldo_pendiente) = 80,000
```

**Causa:** Ventas antiguas que se pagaron pero no se actualizó la deuda del cliente

**Solución:**
```sql
-- Ver la discrepancia
SELECT 
  c.id,
  c.nombre,
  c.saldo_deuda,
  SUM(cs.saldo_pendiente) as suma_ventas
FROM customers c
LEFT JOIN credit_sales cs ON c.id = cs.customer_id
GROUP BY c.id
HAVING c.saldo_deuda != COALESCE(SUM(cs.saldo_pendiente), 0);

-- Corregir (si la suma es correcta)
UPDATE customers SET saldo_deuda = (
  SELECT COALESCE(SUM(saldo_pendiente), 0)
  FROM credit_sales
  WHERE customer_id = customers.id
)
WHERE id = 'UUID';
```

---

### Problema 2: "Pago registrado pero venta sigue en estado 'abierta'"

**Síntomas:**
```
Venta: total=50000, pagado=20000, pendiente=30000
Estado=abierta (debería ser "parcial")
```

**Causa:** El estado no se actualizó correctamente durante el pago

**Solución:**
```sql
-- Verificar estado actual
SELECT * FROM credit_sales WHERE id = 'UUID';

-- Recalcular estado correcto
UPDATE credit_sales 
SET estado = CASE 
    WHEN saldo_pendiente = 0 THEN 'pagada'
    WHEN total_pagado > 0 THEN 'parcial'
    ELSE 'abierta'
  END
WHERE id = 'UUID';
```

---

### Problema 3: "Stock se decrementó pero venta fue rechazada"

**Síntomas:**
```
Stock de Arroz: 100 -> 95 (decrementó)
Pero respuesta API: "400 Bad Request"
SELECT * FROM inventory_movements: SÍ aparece
```

**Causa:** La transacción comenzó pero falló a mitad de camino. Stock se decrementó pero venta no se creó

**Solución:**
Esto debería evitarse con `DB::transaction()` pero si pasa:

```sql
-- 1. Ver el movimiento huérfano
SELECT * FROM inventory_movements 
WHERE credit_sale_id IS NULL 
AND motivo LIKE 'Venta a crédito%';

-- 2. Revertir manualmente si es necesario
UPDATE products 
SET stock = stock + (
  SELECT SUM(cantidad) 
  FROM inventory_movements 
  WHERE product_id = products.id 
  AND credit_sale_id IS NULL
)
WHERE id = 'UUID';

-- 3. Limpiar movimientos
DELETE FROM inventory_movements 
WHERE credit_sale_id IS NULL 
AND motivo LIKE 'Venta a crédito%';
```

---

### Problema 4: "No puedo crear cliente - error de validación"

**Síntomas:**
```
"message": "The given data was invalid",
"errors": {"nombre": ["..."]}
```

**Causas posibles:**
```
1. nombre: vacío, menos de 3 caracteres, o especiales
2. limite_credito: menor a 1000, o texto en lugar de número
3. telefono: más de 20 caracteres
4. direccion: más de 500 caracteres
```

**Solución:** Validar datos antes de enviar:
```javascript
// Antes de POST /api/customers
const cliente = {
  nombre: "Don Juan Pérez",           // ✓ 3-255 chars
  telefono: "999111222",              // ✓ ≤20 chars
  direccion: "Av. Principal 123",     // ✓ ≤500 chars  
  limite_credito: 100000              // ✓ ≥1000
};
```

---

### Problema 5: "Venta a crédito a peso cero o negativo"

**Síntomas:**
```
Venta creada con total = 0 o negativo
```

**Causa:** Algún item tiene precio_unitario = 0 en BD, o cantidad llega como 0

**Solución:**
```bash
# 1. Verificar productos sin precio
SELECT * FROM products WHERE precio_venta = 0 OR precio_venta IS NULL;

# 2. Asignar precios correctos
UPDATE products SET precio_venta = 5000 WHERE precio_venta = 0;

# 3. Para ventas existentes, solo informar (no se pueden editar)
SELECT cs.id, SUM(csi.subtotal) as total_real
FROM credit_sales cs
JOIN credit_sale_items csi ON cs.id = csi.credit_sale_id
WHERE cs.total = 0
GROUP BY cs.id;
```

---

### Problema 6: "Créditos duplicados - cliente aparece dos veces"

**Síntomas:**
```
GET /api/customers?con_deuda=1
Cliente "Don Juan" aparece 2 veces con diferentes IDs
```

**Causa:** Se crearon dos registros para el mismo cliente

**Solución:**
```sql
-- 1. Identificar duplicados
SELECT nombre, COUNT(*) as repeticiones, GROUP_CONCAT(id)
FROM customers
GROUP BY nombre
HAVING COUNT(*) > 1;

-- 2. Merge de ventas (si es necesario)
UPDATE credit_sales 
SET customer_id = 'ID_CORRECTO' 
WHERE customer_id = 'ID_DUPLICADO';

-- 3. Eliminar duplicado
DELETE FROM customers WHERE id = 'ID_DUPLICADO';
```

---

## 🔐 Seguridad y Auditoría

### Verificar quién creó cada venta
```sql
SELECT cs.id, cs.total, u.name as usuario
FROM credit_sales cs
JOIN users u ON u.id = im.user_id
JOIN inventory_movements im ON im.motivo LIKE CONCAT('Venta a crédito #', cs.id)
ORDER BY cs.created_at DESC;
```

### Ver aboños vs cash
```sql
SELECT metodo_pago, COUNT(*) as cantidad, SUM(monto) as total
FROM credit_payments
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY metodo_pago;
```

### Operaciones en el último mes
```sql
-- Ventas creadas
SELECT COUNT(*) as ventas_creadas, SUM(total) as monto_total
FROM credit_sales
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Pagos recibidos
SELECT COUNT(*) as pagos_recibidos, SUM(monto) as monto_total
FROM credit_payments
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 📊 Consultas de Diagnóstico

### Dashboard Rápido
```sql
SELECT 
  'Total Clientes' as metrica, COUNT(*) as valor FROM customers
UNION ALL
SELECT 'Clientes con Deuda', COUNT(*) FROM customers WHERE saldo_deuda > 0
UNION ALL
SELECT 'Deuda Total', SUM(saldo_deuda) FROM customers
UNION ALL
SELECT 'Ventas Abiertas', COUNT(*) FROM credit_sales WHERE estado = 'abierta'
UNION ALL
SELECT 'Ventas en Parcial', COUNT(*) FROM credit_sales WHERE estado = 'parcial'
UNION ALL
SELECT 'Monto Pendiente', SUM(saldo_pendiente) FROM credit_sales WHERE estado != 'pagada';
```

### Clientes Morosos (>30 días sin pagar)
```sql
SELECT 
  c.id, 
  c.nombre, 
  c.saldo_deuda,
  MAX(cs.created_at) as ultima_compra,
  MAX(cp.created_at) as ultimo_pago,
  DATEDIFF(NOW(), MAX(cp.created_at)) as dias_sin_pagar
FROM customers c
JOIN credit_sales cs ON c.id = cs.customer_id
LEFT JOIN credit_payments cp ON cs.id = cp.credit_sale_id
GROUP BY c.id
HAVING DATEDIFF(NOW(), MAX(cp.created_at)) > 30 OR MAX(cp.created_at) IS NULL
ORDER BY dias_sin_pagar DESC;
```

### Ranking de Deudores
```sql
SELECT 
  nombre,
  saldo_deuda,
  limite_credito,
  ROUND((saldo_deuda / limite_credito * 100), 2) as porcentaje_uso,
  estado
FROM customers
WHERE saldo_deuda > 0
ORDER BY saldo_deuda DESC
LIMIT 20;
```

---

## 🚀 Optimización de Rendimiento

### Problemas de lentitud

**El endpoint `/api/customers` es lento:**

```sql
-- Agregar índices
ALTER TABLE customers ADD INDEX idx_estado (estado);
ALTER TABLE customers ADD INDEX idx_saldo_deuda (saldo_deuda);

-- Verificar índices están siendo usados
EXPLAIN SELECT * FROM customers WHERE estado = 'activo';
```

**Las ventas tardan al cargar items:**

```sql
-- Asegurar índices en credit_sale_items
ALTER TABLE credit_sale_items ADD INDEX idx_credit_sale_id (credit_sale_id);

-- En Controllers, verificar eager loading:
// ✓ BIEN: $creditSale->load('items.product', 'customer', 'payments');
// ✗ MAL: foreach($items) { $item->product; } // N+1 queries
```

---

## 🛟 Soporte Técnico

### Pasos para reportar un error

1. **Reproducir el error:**
   - Exactamente qué endpoint llamaste
   - Qué datos enviaste (sin tokens/datos sensibles)
   - Qué respuesta obtuviste

2. **Recopilar información:**
   ```bash
   # Últimos 10 errores en logs
   tail -100 storage/logs/laravel.log | grep -i error
   
   # Check si hay queries lentas
   grep "Slow query" storage/logs/laravel.log
   ```

3. **Verificar base de datos:**
   ```bash
   php artisan tinker
   > Customer::count()     // should = X
   > CreditSale::count()   // should = Y
   > CreditPayment::sum('monto') // should = Z
   ```

---

## 📞 Escalado a Desarrollo

Si después de intentar estas soluciones el problema persiste, proporciona:

```
1. Versión de Laravel: php artisan --version
2. Hash de último commit: git log -1 --oneline
3. Logs de error: tail -50 storage/logs/laravel.log
4. Query SQL que falla (si aplica)
5. Pasos exactos para reproducir
```

---

**¡Espero nunca necesites esta guía! 😄 Pero aquí está por si acaso.** 🆘
