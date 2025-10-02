<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SesionVentanillaService;

class SesionVentanillaController extends Controller
{
    public function iniciar(Request $request, SesionVentanillaService $service)
    {
        $request->validate([
            'fk_sesion_id' => 'required|integer',
            'fk_usuario_id' => 'required|integer',
            'fk_ventanilla_id' => 'required|integer',
        ]);
        try {
            $sesion = $service->iniciarSesionVentanilla(
                $request->fk_sesion_id,
                $request->fk_usuario_id,
                $request->fk_ventanilla_id
            );
            return response()->json($sesion, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function cerrar(Request $request, SesionVentanillaService $service)
    {
        $request->validate([
            'fk_sesion_id' => 'required|integer',
            'fk_usuario_id' => 'required|integer',
            'fk_ventanilla_id' => 'required|integer',
        ]);
        try {
            $service->cerrarSesionVentanilla(
                $request->fk_sesion_id,
                $request->fk_usuario_id,
                $request->fk_ventanilla_id
            );
            return response()->json(['message' => 'Sesión de ventanilla cerrada.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
}
