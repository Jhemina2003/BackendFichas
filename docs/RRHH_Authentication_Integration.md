# Integración de Autenticación RRHH

Este documento describe la implementación completa de la integración con el sistema RRHH para autenticación de usuarios en el Sistema de Fichas.

## Arquitectura

La integración consta de tres componentes principales:

### 1. RrhhService
Servicio responsable de la comunicación con las APIs del sistema RRHH.

**Ubicación:** `app/Services/Auth/RrhhService.php`

**Métodos principales:**
- `validateUser(array $credentials)`: Valida credenciales contra el sistema RRHH
- `getUserDetails(string $usuario, array $tokenData)`: Obtiene datos completos del usuario
- `getOrganizacion(int $organizacionId)`: Obtiene información de la organización

### 2. SignInService
Servicio que orquesta el proceso de autenticación completo.

**Ubicación:** `app/Services/Auth/SignInService.php`

**Métodos principales:**
- `authenticate(array $credentials)`: Proceso completo de autenticación
- `logout(Usuario $usuario)`: Cierre de sesión
- `refreshToken(Usuario $usuario)`: Renovación de token

### 3. AuthController (Actualizado)
Controlador que expone los endpoints de autenticación.

**Ubicación:** `app/Http/Controllers/Api/AuthController.php`

**Endpoints:**
- `POST /api/login`: Autenticación
- `POST /api/logout`: Cierre de sesión
- `GET /api/me`: Información del usuario autenticado
- `POST /api/refresh`: Renovación de token

## Configuración

### Variables de Entorno

Agregar al archivo `.env`:

```env
# RRHH Service Configuration
RRHH_BASE_URL=https://servicios.rree.gob.bo
RRHH_API_KEY=tu_api_key_aqui
RRHH_APLICACION="Sistema Fichas"
RRHH_TIMEOUT=10
RRHH_CACHE_TTL=3600
```

### Configuración de Servicios

La configuración se encuentra en `config/services.php`:

```php
'rrhh' => [
    'base_url' => env('RRHH_BASE_URL', 'https://servicios.rree.gob.bo'),
    'api_key' => env('RRHH_API_KEY'),
    'aplicacion' => env('RRHH_APLICACION', 'Sistema Fichas'),
    'timeout' => env('RRHH_TIMEOUT', 10),
    'cache_ttl' => env('RRHH_CACHE_TTL', 3600),
],
```

## Flujo de Autenticación

### 1. Login
1. El usuario envía credenciales al endpoint `/api/login`
2. `AuthController` valida los datos de entrada
3. `SignInService.authenticate()` es llamado
4. `RrhhService.validateUser()` valida contra RRHH
5. Si es exitoso, se obtienen datos completos del usuario
6. Se crea o actualiza el usuario en la base de datos local
7. Se genera un token Sanctum
8. Se retorna la respuesta con usuario y token

### 2. Gestión de Usuarios Locales
- **Nuevo usuario**: Se crea automáticamente con datos de RRHH
- **Usuario existente**: Se actualizan sus datos con la información más reciente de RRHH
- **Asignación de sucursal**: Se determina basándose en la organización del usuario

### 3. Sesión y Tokens
- Se utiliza Laravel Sanctum para gestión de tokens
- Los tokens tienen una duración de 24 horas
- Cada login invalida tokens anteriores del usuario

## APIs de RRHH Utilizadas

### Autenticación
```
POST {base_url}/autenticacion/api/token
Body: {
    "a": "usuario",
    "b": "password", 
    "c": "Personal",
    "d": "ip_address"
}
```

### Datos de Persona
```
GET {base_url}/api/persona/{persona_id}
```

### Datos de Organización
```
GET {base_url}/api/organizacion/{organizacion_id}
```

## Respuestas de la API

### Login Exitoso
```json
{
    "message": "Autenticación exitosa",
    "data": {
        "user": {
            "usuario_id": 1,
            "usuario": "jperez",
            "nombre_completo": "Juan Carlos Pérez González",
            "correo_electronico": "juan.perez@rree.gob.bo",
            "sucursal": {
                "sucursal_id": 1,
                "nombre": "Santa Cruz"
            },
            "ventanilla": null
        },
        "token": "1|token_generado_por_sanctum",
        "token_type": "Bearer"
    }
}
```

### Error de Autenticación
```json
{
    "message": "Error de autenticación",
    "error": "Credenciales inválidas"
}
```

## Manejo de Errores

### Errores de RRHH
- Timeout de conexión
- Respuestas de error del servicio
- Datos incompletos

### Fallbacks
- Logging detallado de errores
- Respuestas consistentes al cliente
- Manejo gracioso de fallos de red

## Logging

Se registran los siguientes eventos:
- Intentos de autenticación
- Autenticaciones exitosas
- Errores de comunicación con RRHH
- Creación/actualización de usuarios
- Operaciones de tokens

## Seguridad

### Consideraciones Implementadas
- Validación de entrada estricta
- Hashing seguro de contraseñas locales
- Tokens con expiración
- Logging de actividad de autenticación
- Aislamiento por sucursal

### Datos Sensibles
- Las contraseñas no se almacenan en texto plano localmente
- Los tokens RRHH no se almacenan permanentemente
- Se actualiza la contraseña local en cada autenticación

## Testing

Se incluyen tests completos para:
- Autenticación exitosa y fallida
- Creación y actualización de usuarios
- Manejo de errores
- Operaciones de tokens
- Integración completa

**Ejecutar tests:**
```bash
php artisan test tests/Feature/Auth/
php artisan test tests/Unit/Services/Auth/
```

## Mantenimiento

### Monitoreo
- Verificar logs regularmente
- Monitorear tiempos de respuesta de RRHH
- Validar sincronización de datos de usuarios

### Actualizaciones
- Mantener configuración de endpoints actualizada
- Revisar cambios en APIs de RRHH
- Actualizar timeouts según rendimiento

## Notas de Implementación

1. **Sucursal por Defecto**: Si no se puede determinar la organización del usuario, se asigna automáticamente a la primera sucursal disponible.

2. **Actualización Automática**: Los datos del usuario se actualizan en cada login para mantener sincronía con RRHH.

3. **Token Único**: Cada usuario puede tener solo un token activo a la vez.

4. **Compatibilidad**: La implementación mantiene compatibilidad con el sistema de ventanillas existente.

## Próximos Pasos

1. Configurar las URLs y API keys reales de RRHH
2. Realizar pruebas en ambiente de desarrollo
3. Implementar monitoring de la integración
4. Documentar procesos de troubleshooting