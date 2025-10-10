<?php

use Illuminate\Support\Facades\Route;

// Rutas administrativas (solo Administrador)
Route::middleware(['auth:sanctum', 'roles', 'token:Administrador'])->group(function () {
    Route::apiResource('organizaciones', App\Http\Controllers\Api\OrganizacionController::class);
    Route::apiResource('sucursales', App\Http\Controllers\Api\SucursalController::class);
    Route::apiResource('ventanillas', App\Http\Controllers\Api\VentanillaController::class);
    Route::post('ventanillas/{ventanilla_id}/asignar-usuario', [App\Http\Controllers\Api\VentanillaController::class, 'asignarUsuario']);
    Route::apiResource('usuarios', App\Http\Controllers\Api\UsuarioController::class);
    Route::apiResource('rol-usuarios', App\Http\Controllers\Api\RolUsuarioController::class)->only(['store', 'destroy']);
});

// Rutas de ventanilla (Administrador y Ventanilla)
Route::middleware(['auth:sanctum', 'roles', 'token:Administrador,Ventanilla'])->group(function () {
    Route::get('ventanillas/{id}/servicios', [App\Http\Controllers\Api\VentanillaController::class, 'servicios']);
    Route::post('ventanillas/{id}/cerrar', [App\Http\Controllers\Api\VentanillaController::class, 'cerrar']);
    Route::post('ventanillas/{id}/abrir', [App\Http\Controllers\Api\VentanillaController::class, 'abrir']);
    Route::get('ventanillas/todas-cerradas', [App\Http\Controllers\Api\VentanillaController::class, 'todasCerradas']);
    
    // Rutas de atención de fichas
    Route::post('ventanilla/rellamar', [App\Http\Controllers\Api\SeguimientoController::class, 'rellamar']);
    Route::post('ventanilla/en-atencion', [App\Http\Controllers\Api\SeguimientoController::class, 'marcarEnAtencion']);
    Route::post('ventanilla/finalizar', [App\Http\Controllers\Api\SeguimientoController::class, 'marcarFinalizada']);
    Route::post('ventanilla/ausente', [App\Http\Controllers\Api\SeguimientoController::class, 'marcarAusente']);
    Route::post('ventanilla/observacion', [App\Http\Controllers\Api\SeguimientoController::class, 'observacion']);
    Route::post('ventanilla/retornar-espera', [App\Http\Controllers\Api\SeguimientoController::class, 'retornarAEspera']);
    Route::get('ventanilla/ficha-actual', [App\Http\Controllers\Api\VentanillaController::class, 'fichaActual']);
    Route::get('ventanilla/dashboard', [App\Http\Controllers\Api\VentanillaDashboardController::class, 'dashboard']);
    
    Route::post('llamadas/llamar-siguiente', [App\Http\Controllers\Api\LlamadaController::class, 'llamarSiguiente']);
    Route::apiResource('llamadas', App\Http\Controllers\Api\LlamadaController::class);
    Route::apiResource('fichas', App\Http\Controllers\Api\FichaController::class);
    Route::post('sesiones-ventanilla', [App\Http\Controllers\Api\SesionVentanillaController::class, 'iniciar']);
    Route::post('sesiones-ventanilla/cerrar', [App\Http\Controllers\Api\SesionVentanillaController::class, 'cerrar']);
    Route::get('sesiones-ventanilla/estado', [App\Http\Controllers\Api\SesionVentanillaController::class, 'estado']);
});

// Rutas públicas y de lectura
Route::apiResource('dominios', App\Http\Controllers\Api\DominioController::class)->only(['index', 'show']);
Route::apiResource('dominios-grupo', App\Http\Controllers\Api\DominioGrupoController::class)->only(['index', 'show']);
Route::apiResource('roles', App\Http\Controllers\Api\RolController::class)->only(['index', 'show']);
Route::get('fichas/estadisticas', [App\Http\Controllers\Api\FichaController::class, 'estadisticas']);
Route::post('ventanillas/{id}/servicios', [App\Http\Controllers\Api\VentanillaServicioController::class, 'asignarServicios']);

// Otras rutas administrativas
Route::middleware(['auth:sanctum', 'roles'])->group(function () {
    Route::post('sesiones/{id}/reabrir', [App\Http\Controllers\Api\SesionController::class, 'reabrir']);
    Route::apiResource('sesiones', App\Http\Controllers\Api\SesionController::class);
    Route::apiResource('seguimientos', App\Http\Controllers\Api\SeguimientoController::class)->only(['index', 'show', 'store']);
    Route::get('fichas/{ficha}/historial', [App\Http\Controllers\Api\SeguimientoController::class, 'historial']);
    Route::apiResource('asignaciones', App\Http\Controllers\Api\AsignacionController::class);
});

// Rutas de autenticación Sanctum
Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('me', [App\Http\Controllers\Api\AuthController::class, 'me']);
Route::middleware('auth:sanctum')->post('refresh', [App\Http\Controllers\Api\AuthController::class, 'refresh']);

// Rutas para sesiones de ventanilla (duplicadas - eliminar)
// Route::middleware('auth:sanctum')->post('sesiones-ventanilla', [App\Http\Controllers\Api\SesionVentanillaController::class, 'iniciar']);
// Route::middleware('auth:sanctum')->post('sesiones-ventanilla/cerrar', [App\Http\Controllers\Api\SesionVentanillaController::class, 'cerrar']);
// Route::middleware('auth:sanctum')->get('sesiones-ventanilla/estado', [App\Http\Controllers\Api\SesionVentanillaController::class, 'estado']);

// Rutas para gestión de organizaciones y personas desde RRHH (solo Administrador)
Route::middleware(['auth:sanctum', 'roles', 'token:Administrador'])->group(function () {
    Route::get('rrhh/organizaciones', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'listarOrganizaciones']);
    Route::get('rrhh/organizaciones/{id}', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'detalleOrganizacion']);
    Route::get('rrhh/organizaciones/{id}/usuarios', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'usuariosPorOrganizacion']);
    Route::get('rrhh/personas', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'listarPersonas']);
    Route::get('rrhh/organizaciones/{id}/personas', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'personasPorOrganizacion']);
    Route::post('rrhh/usuarios/buscar', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'buscarUsuarios']);
    Route::get('rrhh/usuarios/{id}', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'detalleUsuario']);
    Route::get('rrhh/organizaciones/{id}/sucursales', [App\Http\Controllers\Api\OrganizacionRrhhController::class, 'sucursalesPorOrganizacion']);
});

// Puedes proteger rutas así:
// Route::middleware('auth:sanctum')->get('usuario', function (Request $request) { return $request->user(); });
