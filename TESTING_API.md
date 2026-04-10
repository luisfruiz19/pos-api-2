# 🚀 Guía Rápida: Cómo Probar la API (Genera tu Token)

## Opción 1: Registrarse e iniciar sesión (RECOMENDADO)

### 1️⃣ Registrar nuevo usuario

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'
```

**Respuesta:**
```json
{
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "role": "cajero",
      "created_at": "2026-03-26T10:30:00.000000Z",
      "updated_at": "2026-03-26T10:30:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789",
    "token_type": "Bearer"
  }
}
```

**✅ Guarda el token** en una variable de entorno:

```bash
export TOKEN="1|abcdefghijklmnopqrstuvwxyz123456789"
```

---

### 2️⃣ Login con credenciales

Si ya tienes usuario, usa login:

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "Password123!"
  }'
```

**Respuesta:**
```json
{
  "message": "Autenticación exitosa",
  "data": {
    "user": { ... },
    "token": "2|xyz123...",
    "token_type": "Bearer"
  }
}
```

---

## Opción 2: Token desde la Base de Datos (Para Admin)

Si necesitas acceso rápido sin registrarte:

```bash
# En terminal del servidor
php artisan tinker

>>> $user = User::first();
>>> $user->createToken('auth_token')->plainTextToken;
```

Copias el token y lo usas directamente.

---

## ✅ Usar el Token en Peticiones

Una vez tengas el token, úsalo en todos los endpoints protegidos:

```bash
export TOKEN="tu_token_aqui"

# Ahora puedes hacer peticiones:
curl -X GET http://localhost:8000/api/users/me \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🧪 Script Completo de Pruebas

Crea un archivo `test_api.sh`:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api"
EMAIL="test$(date +%s)@example.com"  # Email único
PASSWORD="Password123!"

echo "╔════════════════════════════════════════════════════════════╗"
echo "║         🚀 PRUEBA COMPLETA DE LA API POS                  ║"
echo "╚════════════════════════════════════════════════════════════╝"

# 1. Registrar usuario
echo ""
echo "📝 1. Registrando nuevo usuario..."
RESPONSE=$(curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d "{
    \"name\": \"Usuario Test\",
    \"email\": \"$EMAIL\",
    \"password\": \"$PASSWORD\",
    \"password_confirmation\": \"$PASSWORD\"
  }")

TOKEN=$(echo $RESPONSE | jq -r '.data.token')
echo "✅ Usuario registrado"
echo "🔐 Token: $TOKEN"
echo ""

# 2. Obtener perfil
echo "👤 2. Obteniendo mi perfil..."
curl -s -X GET $BASE_URL/users/me \
  -H "Authorization: Bearer $TOKEN" | jq

# 3. Ver estadísticas de productos
echo ""
echo "📊 3. Estadísticas de productos..."
curl -s -X GET $BASE_URL/products/stats \
  -H "Authorization: Bearer $TOKEN" | jq

# 4. Crear un producto
echo ""
echo "✨ 4. Creando producto..."
PRODUCT=$(curl -s -X POST $BASE_URL/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Laptop Test",
    "precio_compra": 500,
    "precio_venta": 800,
    "stock": 10,
    "stock_minimo": 2,
    "codigo_barras": "LAP-001"
  }')

PRODUCT_ID=$(echo $PRODUCT | jq -r '.data.id')
echo "✅ Producto creado: $PRODUCT_ID"

# 5. Abrir caja
echo ""
echo "🏧 5. Abriendo caja registradora..."
CAJA=$(curl -s -X POST $BASE_URL/cash-registers/open \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "monto_apertura": 500
  }')

CAJA_ID=$(echo $CAJA | jq -r '.data.id')
echo "✅ Caja abierta: $CAJA_ID"

# 6. Crear venta
echo ""
echo "💳 6. Registrando venta..."
curl -s -X POST $BASE_URL/sales \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"cash_register_id\": \"$CAJA_ID\",
    \"metodo_pago\": \"efectivo\",
    \"items\": [
      {
        \"product_id\": \"$PRODUCT_ID\",
        \"cantidad\": 2
      }
    ]
  }" | jq

# 7. Ver alertas
echo ""
echo "⚠️  7. Verificando alertas..."
curl -s -X GET $BASE_URL/alerts/summary \
  -H "Authorization: Bearer $TOKEN" | jq

# 8. Cerrar caja
echo ""
echo "🏧 8. Cerrando caja..."
curl -s -X POST $BASE_URL/cash-registers/$CAJA_ID/close \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "dinero_contado": 1300,
    "observacion": "Venta completada"
  }' | jq

# 9. Logout
echo ""
echo "🔓 9. Cerrando sesión..."
curl -s -X POST $BASE_URL/auth/logout \
  -H "Authorization: Bearer $TOKEN" | jq

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║              ✅ PRUEBAS COMPLETADAS                        ║"
echo "╚════════════════════════════════════════════════════════════╝"
```

### Ejecutar el script:

```bash
chmod +x test_api.sh
./test_api.sh
```

---

## 📌 Endpoints de Autenticación (SIN PROTECCIÓN)

```
POST   /api/auth/register    - Registrar nuevo usuario
POST   /api/auth/login       - Iniciar sesión (obtener token)
```

## 📌 Otros Endpoints (CON TOKEN)

```
POST   /api/auth/logout      - Cerrar sesión
GET    /api/users/me         - Ver mi perfil
GET    /api/products         - Listar productos
POST   /api/products         - Crear producto
GET    /api/products/stats   - Estadísticas
... (y todos los demás)
```

---

## 🔒 Requisitos de Contraseña

La contraseña debe tener:
- **Mínimo 8 caracteres**
- **Una mayúscula** (A-Z)
- **Una minúscula** (a-z)
- **Un número** (0-9)
- **Un símbolo** (!@#$%^&*)

### ✅ Ejemplos válidos:
- `Password123!`
- `MySecure@Pass99`
- `Test_Api#2024`

### ❌ Ejemplos inválidos:
- `password123` (sin mayúscula)
- `Pass1` (muy corta)
- `PASSWORD` (sin número)

---

## 💡 Tips Útiles

### Guardar Token en Variable
```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"juan@example.com","password":"Password123!"}' | jq -r '.data.token')

echo $TOKEN
```

### Usar con Postman/Insomnia
1. Ir a **Auth** → **Bearer Token**
2. Pegar el token en el campo **Token**
3. Hacer la petición

### Ver Detalles del Token
```bash
curl -s -X GET http://localhost:8000/api/users/me \
  -H "Authorization: Bearer $TOKEN" | jq '.'
```

### Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `401 Unauthorized` | Token inválido o expirado | Genera uno nuevo con login/register |
| `422 Validation failed` | Datos incompletos | Revisa que todos los campos sean válidos |
| `404 Not Found` | Resource ID inválido | Verifica el UUID del recurso |

---

## 🎯 Flujo Completo Recomendado

```
1. Registrarse      → POST /api/auth/register
   ↓
2. Obtener Token    → Guardado en respuesta
   ↓
3. Crear Producto   → POST /api/products (con token)
   ↓
4. Abrir Caja       → POST /api/cash-registers/open
   ↓
5. Hacer Venta      → POST /api/sales
   ↓
6. Ver Alertas      → GET /api/alerts
   ↓
7. Cerrar Caja      → POST /api/cash-registers/{id}/close
   ↓
8. Logout           → POST /api/auth/logout
```

---

¡Ya está listo para probar! 🎉
