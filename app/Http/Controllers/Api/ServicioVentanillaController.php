<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServicioVentanilla;
use App\Models\Usuario;

class ServicioVentanillaController extends Controller
{
    // Obtener servicios activos de la ventanilla asignada al usuario autenticado
    public function index(Request $request)
    {
        $usuario = $request->user();
        $ventanillaId = $usuario->fk_ventanilla_id;
        if (!$ventanillaId) {
            return response()->json([]);
        }
        $servicios = ServicioVentanilla::where('fk_ventanilla_id', $ventanillaId)
            ->where('activo', true)
            ->with('servicio')
            ->get();
        return response()->json($servicios);
    }

    // Actualizar servicios activos de la ventanilla asignada al usuario autenticado
    public function update(Request $request)
    {
        $usuario = $request->user();
        $ventanillaId = $usuario->fk_ventanilla_id;
        if (!$ventanillaId) {
            return response()->json(['message' => 'El usuario no tiene ventanilla asignada'], 400);
        }
        $serviciosSeleccionados = $request->input('servicios', []); // array de fk_servicio_id

        // Desactivar todos los servicios actuales
        ServicioVentanilla::where('fk_ventanilla_id', $ventanillaId)->update(['activo' => false]);

        // Activar los seleccionados
        foreach ($serviciosSeleccionados as $servicioId) {
            ServicioVentanilla::updateOrCreate(
                [
                    'fk_ventanilla_id' => $ventanillaId,
                    'fk_servicio_id' => $servicioId
                ],
                [
                    'activo' => true
                ]
            );
        }
        return response()->json(['message' => 'Servicios de ventanilla actualizados']);
    }
}
