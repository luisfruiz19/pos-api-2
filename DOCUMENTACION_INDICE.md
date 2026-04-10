# 📚 Índice de Documentación API POS

## Para Compartir con Otra IA

### 🎯 Opción 1: Compartir OpenAPI/Swagger (RECOMENDADO)

**Archivo:** `API_OPENAPI.json`

**Cómo usar:**
1. Comparte el archivo directamente al otro modelo
2. Pide que genere código específico: 
   - "Genera un cliente HTTP usando Axios"
   - "Crea componentes Vue para gestionar productos"
   - "Implementa autenticación con el flujo login/logout"

**Ventaja:** Formato estándar, compatible con cualquier herramienta

---

### 🎯 Opción 2: Resumen Ejecutivo

**Archivo:** `API_SUMMARY_FOR_AI.md` 

**Qué contiene:**
- Resumen rápido de endpoints
- Códigos de respuesta
- Roles y permisos
- Estructura de datos
- Flujos típicos

**Cuándo usar:** Cuando necesitas una vista rápida de la API

---

### 🎯 Opción 3: Guía Técnica Completa

**Archivo:** `API_FRONTEND_INTEGRATION.md`

**Qué contiene:**
- Cliente HTTP (Axios) configurado
- Servicios para cada entidad
- Stores Pinia
- Composables Vue 3
- Guardias de ruta
- Manejo de errores

**Cuándo usar:** Cuando necesitas integración Frontend completa

---

## 📂 Estructura de Archivos

```
API_OPENAPI.json                          ← OpenAPI/Swagger
API_SUMMARY_FOR_AI.md                     ← Resumen ejecutivo
API_FRONTEND_INTEGRATION.md               ← Guía técnica
QUICK_START_FRONTEND.md                   ← Ejemplos prácticos
API_DOCUMENTATION.md                      ← Docs originales
DOCUMENTACION_INDICE.md                   ← Este archivo

resources/js/
├── api/
│   └── http.js                           ← Axios configurado
├── services/
│   ├── authService.js
│   ├── productService.js
│   ├── saleService.js
│   ├── cashRegisterService.js
│   ├── alertService.js
│   ├── inventoryService.js
│   └── userService.js
├── stores/
│   ├── authStore.js
│   └── productStore.js
├── composables/
│   └── index.js
└── utils/
    └── helpers.js
```

## 🚀 Flujo de Integración Frontend

### Paso 1: Configuración Inicial
1. Instala dependencias: `npm install axios pinia vue-router`
2. Configura variables de entorno (.env)
3. Copia archivos de `resources/js/`

### Paso 2: Setup Pinia y Router
1. Crea main.js con configuración
2. Crea router/index.js con guardias
3. Importa stores

### Paso 3: Componentes
1. Usa ejemplos de `QUICK_START_FRONTEND.md`
2. Importa servicios correspondientes
3. Usa stores para estado global

### Paso 4: Testing
1. Login test
2. CRUD productos
3. Crear venta
4. Abrir/Cerrar caja

---

## 📋 Checklist para Verificar Integración

- [ ] Axios configurado con base URL correcta
- [ ] Token guardado en localStorage tras login
- [ ] Interceptor de response maneja 401
- [ ] Stores Pinia inicializados
- [ ] Router con guardias de autenticación
- [ ] Componentes de login funcionales
- [ ] Listado de productos renderiza
- [ ] Crear producto guarda en BD
- [ ] Crear venta decrementa stock
- [ ] Abrir/Cerrar caja funciona
- [ ] Alertas se muestran
- [ ] Errores 422 muestran validaciones

---

## 🔒 Seguridad - Implementar

- [ ] HTTPS en producción
- [ ] CORS configurado correctamente
- [ ] Rate limiting en API
- [ ] Validación de entrada (Frontend y Backend)
- [ ] Sanitizar datos antes de mostrar
- [ ] Refresh tokens (implementar si sesiones largas)
- [ ] Logout automático por inactividad

---

## 📊 Ejemplo de Flujo: Crear una Venta

```
Usuario clicks "New Sale"
    ↓
Frontend obtiene: GET /cash-registers/my-open-register
    ↓
Muestra formulario de búsqueda de productos
    ↓
Usuario busca productos: GET /products?search=...
    ↓
Usuario selecciona productos y cantidades
    ↓
Usuario confirma: POST /sales
  {
    "cash_register_id": "...",
    "metodo_pago": "efectivo",
    "items": [{"product_id": "...", "cantidad": 2}]
  }
    ↓
Backend:
  - Valida caja abierta ✓
  - Valida stock disponible ✓
  - Decrementa stock ✓
  - Crea SaleDetail ✓
  - Genera alertas si stock bajo ✓
  - Retorna venta con detalles
    ↓
Frontend:
  - Limpia formulario
  - Muestra confirmación
  - Actualiza lista de ventas
```

---

## 📝 Documentación por Recurso

### Productos
**Operaciones:** CRUD completo + búsqueda
**Filtros:** estado, stock bajo, stock cero
**Búsqueda:** por nombre o código de barras
**Archivo de referencia:** `API_OPENAPI.json` (path: `/products`)

### Ventas
**Operaciones:** Crear, listar, obtener, reportes
**Reportes:** Por fecha, por método de pago, por usuario
**Requisito:** Caja abierta
**Archivo de referencia:** `API_OPENAPI.json` (path: `/sales`)

### Cajas Registradoras
**Operaciones:** Abrir, cerrar, resumen
**Estados:** abierta, cerrada
**Resumen incluye:** total esperado vs contado, diferencia
**Archivo de referencia:** `API_OPENAPI.json` (path: `/cash-registers`)

### Inventario
**Operaciones:** Registrar movimientos, historial
**Tipos de movimiento:** entrada, salida, ajuste
**Reportes:** por producto, por período
**Archivo de referencia:** `API_OPENAPI.json` (path: `/inventory-movements`)

### Alertas
**Niveles:** info, warning, critical
**Tipos:** stock_bajo, agotado, venta_importante, otro
**Automáticas:** Se generan en eventos
**Archivo de referencia:** `API_OPENAPI.json` (path: `/alerts`)

### Usuarios
**Roles:** admin, cajero
**Operaciones:** CRUD, cambio de contraseña
**Solo admin puede:** Criar/eliminar usuarios, cambiar roles
**Archivo de referencia:** `API_OPENAPI.json` (path: `/users`)

---

## 🧪 Ejemplos de Testing

### Test Login
```javascript
POST http://localhost:8000/api/auth/login
{
  "email": "admin@example.com",
  "password": "password123"
}
// Respuesta: { data: { user, token, token_type } }
```

### Test Crear Producto
```javascript
POST http://localhost:8000/api/products
Authorization: Bearer {token}
{
  "nombre": "Laptop",
  "precio_compra": 500,
  "precio_venta": 800,
  "stock": 10,
  "stock_minimo": 2,
  "codigo_barras": "SKU-001"
}
```

### Test Crear Venta
```javascript
POST http://localhost:8000/api/sales
Authorization: Bearer {token}
{
  "cash_register_id": "{uuid}",
  "metodo_pago": "efectivo",
  "items": [
    {
      "product_id": "{uuid}",
      "cantidad": 2
    }
  ]
}
```

---

## 🤖 Prompts para Usar con IAs

### Para Generar Cliente HTTP
> "Tengo una API REST con autenticación Bearer. Necesito un cliente HTTP robusto con interceptadores para manejar tokens expirados. Aquí está la especificación OpenAPI: [pega API_OPENAPI.json]. Genera un cliente Axios typed (TypeScript si es posible)."

### Para Generar Componentes Vue
> "Necesito componentes Vue 3 para integrar con una API POS. Usa esta guía de integración: [pega API_FRONTEND_INTEGRATION.md]. Crea un componente para gestionar productos (CRUD) con búsqueda y paginación."

### Para Generar Store Pinia
> "Crea stores Pinia para manejar estado de: autenticación, productos, ventas, cajas registradoras. Usa los servicios del cliente HTTP que ya proporcioné."

### Para Generar Formularios de Validación
> "Necesito formularios Vue con validación para: crear producto, crear usuario, crear venta. Aquí está el esquema de la API: [pega API_OPENAPI.json]. Incluye mensajes de error claros."

---

## 📞 Diagnóstico de Problemas Comunes

**Problema: "Token inválido / 401"**
- Verifica que localStorage tiene `auth_token`
- Revisa header `Authorization: Bearer {token}`
- Token puede estar expirado

**Problema: "Error 422 en validación"**
- Revisa objeto `errors` en respuesta
- Valida tipos de datos (ej: números no deben ser strings)
- Campos requeridos están presente

**Problema: "CORS error"**
- Verifica VITE_API_BASE_URL en .env
- API debe tener CORS configurado
- Browser envía preflight OPTIONS

**Problema: "Stock no se decrementa"**
- Verifica que POST /sales completó exitosamente (201)
- Revisa que product_id es UUID válido
- Verifica cantidad no excede stock disponible

---

## 📚 Referencias

- OpenAPI Spec: https://swagger.io/specification/
- Axios Docs: https://axios-http.com/
- Pinia Docs: https://pinia.vuejs.org/
- Vue 3 Docs: https://vuejs.org/

---

## 📅 Cambios Recientes

- 2026-04-01: Documentación completa creada
- Incluidos archivos de servicio para Frontend
- Ejemplos de componentes Vue listos
- OpenAPI 3.0 generado

---

**Última actualización:** 2026-04-01
