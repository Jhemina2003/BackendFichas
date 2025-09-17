<?php

use Illuminate\Support\Facades\Route;

// Ejemplo de rutas para la API
Route::apiResource('usuarios', App\Http\Controllers\Api\UsuarioController::class);
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
Route::apiResource('sesiones', App\Http\Controllers\Api\SesionController::class);
Route::apiResource('seguimientos', App\Http\Controllers\Api\SeguimientoController::class)->only(['index', 'show', 'store']);
