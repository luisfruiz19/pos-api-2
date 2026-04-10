# 🔐 Autenticación - Resumen Rápido

## ✅ Lo que se agregó

### Archivos Creados:
1. **AuthController.php** - Maneja login, register, logout
2. **LoginRequest.php** - Valida credenciales
3. **RegisterRequest.php** - Valida nuevo usuario (con Password::class)

### Rutas Agregadas (3 endpoints):
```
POST   /api/auth/register    ← Registro (sin protección)
POST   /api/auth/login       ← Login (sin protección)
POST   /api/auth/logout      ← Logout (protegido)
```

---

## 🚀 Cómo Hacer una Prueba Rápida

### Opción A: Con curl

```bash
# 1. REGISTRARSE
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan",
    "email": "juan@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'

# Respuesta: { "data": { "token": "1|abc..." } }
# Copias el token

# 2. USARLO EN CUALQUIER REQUEST
curl -X GET http://localhost:8000/api/users/me \
  -H "Authorization: Bearer 1|abc..."
```

### Opción B: Con Postman/Insomnia

1. POST → `http://localhost:8000/api/auth/register`
2. Body → Raw JSON:
```json
{
  "name": "Juan",
  "email": "juan@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```
3. Copias el `token` de la respuesta
4. En el siguiente request: **Auth** → **Bearer Token** → pega el token

---

## 📋 Requisitos de Validación

### Contraseña:
- Mínimo 8 caracteres
- 1 mayúscula (A-Z)
- 1 minúscula (a-z)
- 1 número (0-9)
- 1 símbolo (!@#$%^&*)

**Ejemplo válido:** `Password123!`

### Email:
- Debe ser válido
- Debe ser único (no existir)

### Name:
- Mínimo 1 carácter
- Máximo 255 caracteres

---

## 🔄 Flujo de Uso

```
1. Registrarse/Login
   ↓
2. Recibir token
   ↓
3. Usar token en header: Authorization: Bearer <token>
   ↓
4. Acceder a todos los endpoints protegidos
```

---

## 💡 Notas Importantes

1. **El token es válido por siempre** hasta que hagas logout o lo revokes
2. **Cada usuario** puede tener múltiples tokens activos simultáneamente
3. **El logout** invalida solo el token actual
4. **Los endpoints sin autenticación** son solo:
   - `POST /api/auth/register`
   - `POST /api/auth/login`

---

## 📚 Total de Endpoints Ahora

- **3 endpoints** de autenticación (sin protección: register, login)
- **1 endpoint** protegido (logout)
- **40 endpoints** del negocio protegidos por Sanctum

**TOTAL: 44 rutas**

---

## 🎯 Próximo Paso

Para probar la API completa, ejecuta:

```bash
chmod +x test_api.sh
./test_api.sh
```

(Ver archivo `TESTING_API.md` para más detalles)
