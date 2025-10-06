<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SesionVentanillaService;

class SesionVentanillaController extends Controller
{
    public function iniciar(Request $request, SesionVentanillaService $service)
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->fk_ventanilla_id) {
            return response()->json(['message' => 'Usuario no autenticado o sin ventanilla asignada'], 403);
        }
        
        try {
            $sesion = $service->iniciarSesionVentanilla(
                null, // fk_sesion_id se busca/crea automáticamente
                $usuario->usuario_id,
                $usuario->fk_ventanilla_id
            );
            return response()->json($sesion, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function cerrar(Request $request, SesionVentanillaService $service)
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->fk_ventanilla_id) {
            return response()->json(['message' => 'Usuario no autenticado o sin ventanilla asignada'], 403);
        }
        
        try {
            // Buscar la sesión activa del usuario
            $sesionVentanilla = \App\Models\SesionVentanilla::where('fk_usuario_id', $usuario->usuario_id)
                ->where('fk_ventanilla_id', $usuario->fk_ventanilla_id)
                ->where('estado', 'activa')
                ->first();
                
            if (!$sesionVentanilla) {
                return response()->json(['message' => 'No hay sesión activa para cerrar'], 404);
            }
            
            $service->cerrarSesionVentanilla(
                $sesionVentanilla->fk_sesion_id,
                $usuario->usuario_id,
                $usuario->fk_ventanilla_id
            );
            return response()->json(['message' => 'Sesión de ventanilla cerrada.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
}
