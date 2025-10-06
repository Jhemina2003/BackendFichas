// Dashboard de ventanilla (protegido)
Route::middleware('auth:sanctum')->get('ventanilla/dashboard', [App\Http\Controllers\Api\VentanillaDashboardController::class, 'dashboard']);
<?php

use Illuminate\Support\Facades\Route;

// Ejemplo de rutas para la API
Route::apiResource('usuarios', App\Http\Controllers\Api\UsuarioController::class);
// Rutas específicas de fichas (deben ir ANTES del apiResource)
Route::get('fichas/estadisticas', [App\Http\Controllers\Api\FichaController::class, 'estadisticas']);
Route::apiResource('fichas', App\Http\Controllers\Api\FichaController::class);
Route::apiResource('llamadas', App\Http\Controllers\Api\LlamadaController::class);
Route::post('llamadas/llamar-siguiente', [App\Http\Controllers\Api\LlamadaController::class, 'llamarSiguiente']);
Route::apiResource('dominios', App\Http\Controllers\Api\DominioController::class)->only(['index', 'show']);
Route::apiResource('dominios-grupo', App\Http\Controllers\Api\DominioGrupoController::class)->only(['index', 'show']);
Route::apiResource('roles', App\Http\Controllers\Api\RolController::class)->only(['index', 'show']);
Route::apiResource('rol-usuarios', App\Http\Controllers\Api\RolUsuarioController::class)->only(['store', 'destroy']);
Route::apiResource('ventanillas', App\Http\Controllers\Api\VentanillaController::class);
Route::post('ventanillas/{id}/cerrar', [App\Http\Controllers\Api\VentanillaController::class, 'cerrar']);
Route::post('ventanillas/{id}/abrir', [App\Http\Controllers\Api\VentanillaController::class, 'abrir']);
Route::get('ventanillas/todas-cerradas', [App\Http\Controllers\Api\VentanillaController::class, 'todasCerradas']);
Route::apiResource('sucursales', App\Http\Controllers\Api\SucursalController::class);
Route::apiResource('organizaciones', App\Http\Controllers\Api\OrganizacionController::class);
Route::post('sesiones/{id}/reabrir', [App\Http\Controllers\Api\SesionController::class, 'reabrir']);
Route::apiResource('sesiones', App\Http\Controllers\Api\SesionController::class);
Route::apiResource('seguimientos', App\Http\Controllers\Api\SeguimientoController::class)->only(['index', 'show', 'store']);
// Acciones de atención sobre fichas
Route::post('fichas/{ficha}/en-atencion', [App\Http\Controllers\Api\SeguimientoController::class, 'marcarEnAtencion']);
Route::post('fichas/{ficha}/finalizar', [App\Http\Controllers\Api\SeguimientoController::class, 'marcarFinalizada']);
Route::post('fichas/{ficha}/ausente', [App\Http\Controllers\Api\SeguimientoController::class, 'marcarAusente']);
Route::post('fichas/{ficha}/reasignar', [App\Http\Controllers\Api\SeguimientoController::class, 'reasignarFicha']);
Route::get('fichas/{ficha}/historial', [App\Http\Controllers\Api\SeguimientoController::class, 'historial']);
Route::apiResource('asignaciones', App\Http\Controllers\Api\AsignacionController::class);

// Rutas de autenticación Sanctum
Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);



// Rutas para sesiones de ventanilla
Route::middleware('auth:sanctum')->post('sesiones-ventanilla', [App\Http\Controllers\Api\SesionVentanillaController::class, 'iniciar']);
Route::middleware('auth:sanctum')->post('sesiones-ventanilla/cerrar', [App\Http\Controllers\Api\SesionVentanillaController::class, 'cerrar']);

// Puedes proteger rutas así:
// Route::middleware('auth:sanctum')->get('usuario', function (Request $request) { return $request->user(); });
