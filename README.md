# 🛍️ POS API - Sistema de Punto de Venta

`Sistema de Punto de Venta (POS) REST API desarrollado con Laravel 11 + Sanctum`

## 📋 Descripción

API REST completa para gestionar un punto de venta con:
- ✅ Gestión de productos con código de barras
- ✅ Sistema de ventas con múltiples métodos de pago
- ✅ Control de cajas registradoras
- ✅ Gestión de inventario con alertas
- ✅ Sistema de alertas inteligente
- ✅ Autenticación segura con Sanctum
- ✅ Roles y permisos (admin/cajero)

## 🚀 Stack Tecnológico

**Backend:**
- Laravel 11
- PHP 8.3+
- SQLite/MySQL
- API REST

**Frontend (Incluido):**
- Vue 3
- Vite
- Pinia
- Axios
- Vue Router

## 📚 Documentación

### 🔴 IMPORTANTE - Para Compartir con Otra IA

**Usa este archivo:** [`API_OPENAPI.json`](API_OPENAPI.json)
- Especificación completa en OpenAPI 3.0
- Compatible con cualquier herramienta
- Todos los endpoints, parámetros, ejemplos

**Ejemplo de prompt:**
```
Eres experto en integración. Esta es mi API:
[Pega el contenido de API_OPENAPI.json]

Necesito: [tu tarea específica]
```

### 📖 Documentación Disponible

| Documento | Descripción | Para Quién |
|-----------|-----------|-----------|
| [API_OPENAPI.json](API_OPENAPI.json) | Especificación OpenAPI 3.0 | IAs, Postman, Swagger UI |
| [API_SUMMARY_FOR_AI.md](API_SUMMARY_FOR_AI.md) | Resumen ejecutivo | Otras IAs / Desarrolladores |
| [QUICK_START_FRONTEND.md](QUICK_START_FRONTEND.md) | Ejemplos Vue listos | Desarrollador Frontend |
| [API_FRONTEND_INTEGRATION.md](API_FRONTEND_INTEGRATION.md) | Guía técnica completa | Integración Frontend |
| [FRONTEND_SETUP_GUIDE.md](FRONTEND_SETUP_GUIDE.md) | Setup paso a paso | Desarrollo local |
| [DOCUMENTACION_INDICE.md](DOCUMENTACION_INDICE.md) | Índice navegable | Todos |
| [DOCUMENTACION_COMPLETADA.md](DOCUMENTACION_COMPLETADA.md) | Resumen de todo | Referencia rápida |

## 🔌 API Endpoints

### Autenticación
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
```

### Productos
```
GET    /api/products
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
GET    /api/products/stats
```

### Ventas
```
GET    /api/sales
POST   /api/sales
GET    /api/sales/report
```

### Cajas Registradoras
```
GET    /api/cash-registers
POST   /api/cash-registers/open
POST   /api/cash-registers/{id}/close
```

### Más endpoints...
Ver documentación completa en [API_OPENAPI.json](API_OPENAPI.json)

## 🔧 Instalación

### Backend
```bash
# Clonar repository
git clone <repo>
cd pos-api-2

# Instalar dependencias
composer install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate
php artisan db:seed

# Iniciar servidor
php artisan serve
```

### Frontend
```bash
# Instalar dependencias
npm install

# Configurar variables
cp .env.frontend.example .env.local

# Desarrollar
npm run dev

# Build
npm run build
```

## 📂 Estructura Backend

```
app/
├── Http/
│   ├── Controllers/API/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── SaleController.php
│   │   ├── CashRegisterController.php
│   │   ├── InventoryMovementController.php
│   │   ├── AlertController.php
│   │   └── UserController.php
│   └── Requests/ (Form Requests)
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Sale.php
│   ├── CashRegister.php
│   ├── InventoryMovement.php
│   └── Alert.php
└── Policies/ (Autorización)

routes/
└── api.php (Rutas API)

database/
├── migrations/
└── factories/ (Seeders)
```

## 📂 Estructura Frontend

```
resources/js/
├── api/http.js                 ← Cliente HTTP
├── services/                   ← Servicios API
│   ├── authService.js
│   ├── productService.js
│   ├── saleService.js
│   ├── cashRegisterService.js
│   ├── alertService.js
│   ├── inventoryService.js
│   └── userService.js
├── stores/                     ← State Pinia
│   ├── authStore.js
│   └── productStore.js
├── composables/               ← Composables Vue
└── utils/helpers.js           ← Funciones auxiliares
```

## 🔐 Autenticación

API usa **Bearer Tokens (Sanctum)**

```
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "data": {
    "user": { ... },
    "token": "your_token_here",
    "token_type": "Bearer"
  }
}
```

Usar en requests:
```
Authorization: Bearer {token}
```

## 💾 Base de Datos

**Entidades:**
- Users (admin, cajero)
- Products (con código de barras)
- Sales (con detalles)
- CashRegisters (estados: abierta/cerrada)
- InventoryMovements (entrada, salida, ajuste)
- Alerts (automáticas)

**Relaciones:**
```
User → CashRegister → Sale → SaleDetail → Product
    → InventoryMovement → Product
    → Alert
```

## 🧪 Testing

### Test Manual con cURL
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Listar productos
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}"
```

### Test con Postman
1. Importar `API_OPENAPI.json`
2. Configurar variable `token` tras login
3. Ejecutar requests

## 🚀 Deploy

### Production
```bash
# Build
npm run build

# Configurar .env para producción
php artisan config:cache

# Servicios en segundo plano
php artisan queue:work
```

### Hosting
- Backend: cualquier hosting PHP (Heroku, DigitalOcean, etc)
- Frontend: Vercel, Netlify, GitHub Pages

## 📊 Casos de Uso

### 1. Crear Venta
```
1. GET /cash-registers/my-open-register
2. GET /products (buscar)
3. POST /sales (crear con items)
4. ✅ Stock descontado automáticamente
```

### 2. Gestionar Stock
```
1. GET /products/low-stock
2. POST /inventory-movements (registrar entrada/salida)
3. ✅ Alertas generadas automáticamente
```

### 3. Cierre de Caja
```
1. GET /cash-registers/{id}/summary
2. POST /cash-registers/{id}/close (dinero contado)
3. ✅ Diferencias registradas
```

## 🎯 Migrar Frontend

### Paso 1: Copiar Servicios
Copiar `resources/js/` a tu proyecto Vue

### Paso 2: Instalar Dependencias
```bash
npm install axios pinia vue-router
```

### Paso 3: Usar Servicios
```javascript
import { productService } from '@/services/productService'
import { useProductStore } from '@/stores/productStore'

// En componente
const products = await productService.getAll()
// o
const products = computed(() => productStore.products)
```

Ver [QUICK_START_FRONTEND.md](QUICK_START_FRONTEND.md) para ejemplos completos.

## ❓ Preguntas Frecuentes

**¿Cómo comparto con otra IA?**
→ Usa [API_OPENAPI.json](API_OPENAPI.json)

**¿Cómo integro el frontend?**
→ Lee [QUICK_START_FRONTEND.md](QUICK_START_FRONTEND.md)

**¿Cómo configuro el desarrollo?**
→ Sigue [FRONTEND_SETUP_GUIDE.md](FRONTEND_SETUP_GUIDE.md)

**¿Dónde veo todos los endpoints?**
→ [API_OPENAPI.json](API_OPENAPI.json) o [API_SUMMARY_FOR_AI.md](API_SUMMARY_FOR_AI.md)

## 🤝 Contribuciones

El proyecto está documentado para ser fácil de:
- Entender
- Extender
- Integrar con otros sistemas
- Compartir con otros desarrolladores

## 📝 Versión

- API: **v1.0.0**
- Documentación: **v1.0**
- Framework: **Laravel 11**
- Última actualización: **2026-04-01**

## 📞 Soporte

Para problemas comunes, ver:
- Troubleshooting: [DOCUMENTACION_INDICE.md](DOCUMENTACION_INDICE.md#-diagnóstico-de-problemas-comunes)
- Setup: [FRONTEND_SETUP_GUIDE.md](FRONTEND_SETUP_GUIDE.md#troubleshooting)
- Errores API: [API_SUMMARY_FOR_AI.md](API_SUMMARY_FOR_AI.md#-códigos-de-respuesta)

---

**¡Listo! Tu API está completamente documentada y lista para integración.** 🎉

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
