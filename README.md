## Resumen de optimizaciones y funcionamiento del proyecto

Este backend implementa las siguientes reglas y optimizaciones:

### 1. Normalización y relaciones
- Todas las entidades principales (usuarios, ventanillas, sucursales, organizaciones, asignaciones, fichas, dominios, roles) están separadas y relacionadas por claves foráneas.
- Los catálogos de tipos, estados y prioridades se gestionan en tablas de dominio.

### 2. Restricciones de integridad
- Restricciones de unicidad y not null en campos críticos (usuario, correo, nombre de organización, número de ventanilla por sucursal, etc.).
- Índices en todas las claves foráneas y campos de búsqueda frecuente.

### 3. Soft deletes
- Implementados en ventanillas, sucursales, organizaciones, asignaciones, usuarios, dominios y roles. Los registros eliminados no se pierden y pueden restaurarse si se implementa el endpoint correspondiente.

### 4. Consultas eficientes
- Todos los endpoints que devuelven listas usan Eloquent con `with()` para cargar relaciones y evitar el problema N+1.
- Se recomienda usar paginación en endpoints con muchos registros.

### 5. Validaciones robustas
- Validaciones en migraciones y en los controladores para asegurar integridad y evitar datos duplicados o inválidos.

### 6. Estados y enums
- Los estados de ventanilla y otros catálogos usan enums o tablas de dominio para evitar errores de tipeo y mantener consistencia.

### 7. Roles y permisos
- El backend está preparado para aplicar políticas de acceso según el rol (Super Administrador, Administrador, Operador), aunque la autenticación aún no está implementada.

### 8. Documentación y ejemplos
- El README incluye ejemplos de endpoints, filtros, respuestas y errores para facilitar el consumo desde frontend.

### 9. Manejo de errores
- Todas las respuestas de error y validación son claras y uniformes, con mensajes y códigos HTTP apropiados.

### 10. Migraciones y seeders
- El proyecto incluye migraciones y seeders para poblar la base de datos con datos de prueba y catálogos iniciales.

---
**Nota:** La autenticación por tokens aún no está implementada. Si se requiere, se recomienda usar Laravel Sanctum o Passport y proteger los endpoints sensibles con el middleware `auth:sanctum` o `auth:api`.
## API BackendFichas (Documentación Completa)

### Autenticación
- La API requiere autenticación por token (Bearer) para la mayoría de los endpoints protegidos.
- Incluye el header: `Authorization: Bearer {token}` en tus requests.

### Roles y permisos
- **Super Administrador:** Puede crear organizaciones, sucursales, ventanillas, tipos de servicio, asignar roles y usuarios, descargar reportes generales e individuales.
- **Administrador:** Puede gestionar usuarios y ventanillas de su sucursal, asignar tipos de servicio a ventanillas, ver reportes de su sucursal y personales.
- **Operador:** Solo puede operar en su ventanilla y ver sus propios reportes.

### Recursos principales y endpoints

#### Ventanillas
- `GET /api/ventanillas` — Lista ventanillas (filtros: `estado`, `sucursal_id`, `numero`).
- `POST /api/ventanillas` — Crea una ventanilla.
- `POST /api/ventanillas/{id}/cerrar` — Cierra una ventanilla.
- `POST /api/ventanillas/{id}/abrir` — Abre una ventanilla.
- `POST /api/ventanillas/{id}/restore` — Restaura una ventanilla eliminada (si implementado).

#### Sucursales
- `GET /api/sucursales` — Lista sucursales (filtro: `organizacion_id`).
- `POST /api/sucursales` — Crea una sucursal.
- `POST /api/sucursales/{id}/restore` — Restaura una sucursal eliminada (si implementado).

#### Organizaciones
- `GET /api/organizaciones` — Lista organizaciones.
- `POST /api/organizaciones` — Crea una organización.
- `POST /api/organizaciones/{id}/restore` — Restaura una organización eliminada (si implementado).

#### Usuarios
- `GET /api/usuarios` — Lista usuarios (filtros: sucursal, tipo de servicio, nombre, correo, activo).
- `POST /api/usuarios` — Crea un usuario.
- `PUT /api/usuarios/{id}` — Actualiza un usuario.
- `DELETE /api/usuarios/{id}` — Elimina un usuario (soft delete).

#### Asignaciones
- `GET /api/asignaciones` — Lista asignaciones (filtros: usuario, sucursal, ventanilla, organización, activo).
- `POST /api/asignaciones` — Crea una asignación.
- `PUT /api/asignaciones/{id}` — Actualiza una asignación.
- `DELETE /api/asignaciones/{id}` — Elimina una asignación (soft delete).
- `POST /api/asignaciones/{id}/restore` — Restaura una asignación eliminada (si implementado).

#### Fichas
- `GET /api/fichas` — Lista fichas (filtros: sesión, tipo, prioridad, fecha, estado, usuario, sucursal).
- `POST /api/fichas` — Crea una ficha.
- `POST /api/fichas/{ficha}/reasignar` — Reasigna una ficha a otra ventanilla.

#### Seguimientos
- `GET /api/seguimientos` — Lista seguimientos.
- `POST /api/seguimientos` — Crea un seguimiento.

#### Roles y dominios
- `GET /api/roles` — Lista roles.
- `GET /api/dominios` — Lista dominios (tipos, estados, prioridades, etc.).

### Ejemplo de error de validación
```json
{
	"message": "The given data was invalid.",
	"errors": {
		"fk_sucursal_id": ["El campo sucursal es obligatorio."],
		"numero": ["El número ya está en uso en esta sucursal."]
	}
}
```

### Ejemplo de error de autenticación
```json
{
	"message": "Unauthenticated."
}
```

### Paginación
- Usa el parámetro `?page=1` en los endpoints que soportan paginación.
- Respuesta típica:
```json
{
	"current_page": 1,
	"data": [ ... ],
	"last_page": 5,
	"per_page": 20,
	"total": 100
}
```

### Notas de uso
- Todos los endpoints devuelven respuestas JSON.
- Los errores y validaciones se devuelven con mensajes claros y códigos HTTP apropiados.
- Los endpoints protegidos requieren autenticación.
- Los registros eliminados con soft delete pueden restaurarse si se implementa el endpoint correspondiente.
- Consulta la base de datos de dominios para obtener los valores válidos de tipos, estados y prioridades.

---
## API BackendFichas

### Endpoints principales

#### Ventanillas
- `GET /api/ventanillas` — Lista todas las ventanillas (puedes filtrar por `estado`, `sucursal_id`, `numero`).
- `POST /api/ventanillas` — Crea una ventanilla. Body ejemplo:
	```json
	{
		"fk_sucursal_id": 1,
		"numero": 5,
		"estado": "abierta"
	}
	```
- `POST /api/ventanillas/{id}/cerrar` — Cierra una ventanilla.
- `POST /api/ventanillas/{id}/abrir` — Abre una ventanilla.

#### Usuarios
- `GET /api/usuarios` — Lista usuarios (puedes filtrar por sucursal, tipo de servicio, nombre, correo, activo).
- `POST /api/usuarios` — Crea un usuario.

#### Asignaciones
- `GET /api/asignaciones` — Lista asignaciones (puedes filtrar por usuario, sucursal, ventanilla, organización, activo).
- `POST /api/asignaciones` — Crea una asignación.

#### Fichas
- `GET /api/fichas` — Lista fichas (filtros: sesión, tipo, prioridad, fecha, estado, usuario, sucursal).
- `POST /api/fichas` — Crea una ficha.
- `POST /api/fichas/{ficha}/reasignar` — Reasigna una ficha a otra ventanilla.

#### Ejemplo de respuesta de ventanilla
```json
{
	"ventanilla_id": 1,
	"fk_sucursal_id": 1,
	"numero": 1,
	"estado": "abierta",
	"created_at": "2025-09-25T12:00:00Z",
	"updated_at": "2025-09-25T12:00:00Z"
}
```

### Notas
- Todos los endpoints devuelven respuestas JSON.
- Los errores y validaciones se devuelven con mensajes claros y códigos HTTP apropiados.
- Para endpoints protegidos, asegúrate de enviar el token de autenticación si aplica.

---
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
