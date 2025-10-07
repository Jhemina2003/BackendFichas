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
            
            return response()->json([
                'message' => 'Sesión de ventanilla cerrada.',
                'sesion_general_cerrada' => $service->todasCerradas($sesionVentanilla->fk_sesion_id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    /**
     * Obtener estado de sesiones de ventanilla de la sucursal del usuario
     */
    public function estado(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->fk_sucursal_id) {
            return response()->json(['message' => 'Usuario no autenticado o sin sucursal asignada'], 403);
        }

        // Buscar sesión activa de la sucursal
        $sesionGeneral = \App\Models\Sesion::where('fk_sucursal_id', $usuario->fk_sucursal_id)
            ->whereDate('fecha', now()->toDateString())
            ->with('estadoDominio')
            ->first();

        if (!$sesionGeneral) {
            return response()->json([
                'sesion_general' => null,
                'ventanillas' => []
            ]);
        }

        // Obtener estado de todas las ventanillas de la sucursal
        $ventanillas = \App\Models\Ventanilla::where('fk_sucursal_id', $usuario->fk_sucursal_id)
            ->with(['usuario'])
            ->get()
            ->map(function($ventanilla) use ($sesionGeneral) {
                $sesionVentanilla = \App\Models\SesionVentanilla::where('fk_sesion_id', $sesionGeneral->sesion_id)
                    ->where('fk_ventanilla_id', $ventanilla->ventanilla_id)
                    ->where('estado', 'activa')
                    ->with('usuario')
                    ->first();

                return [
                    'ventanilla_id' => $ventanilla->ventanilla_id,
                    'numero' => $ventanilla->numero,
                    'estado_ventanilla' => $ventanilla->estado,
                    'sesion_activa' => $sesionVentanilla ? true : false,
                    'usuario_actual' => $sesionVentanilla ? $sesionVentanilla->usuario->nombre_completo : null,
                    'hora_inicio' => $sesionVentanilla ? $sesionVentanilla->hora_inicio : null
                ];
            });

        return response()->json([
            'sesion_general' => [
                'sesion_id' => $sesionGeneral->sesion_id,
                'fecha' => $sesionGeneral->fecha,
                'estado' => $sesionGeneral->estadoDominio->nombre ?? 'desconocido'
            ],
            'ventanillas' => $ventanillas,
            'total_ventanillas_activas' => $ventanillas->where('sesion_activa', true)->count()
        ]);
    }
}
