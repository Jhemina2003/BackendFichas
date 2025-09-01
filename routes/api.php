<?php

use Illuminate\Support\Facades\Route;

// Ejemplo de rutas para la API
Route::apiResource('usuarios', App\Http\Controllers\Api\UsuarioController::class);
Route::apiResource('fichas', App\Http\Controllers\Api\FichaController::class);
Route::apiResource('llamadas', App\Http\Controllers\Api\LlamadaController::class);
Route::apiResource('asignaciones', App\Http\Controllers\Api\AsignacionController::class);
// Agrega más rutas según tus modelos
