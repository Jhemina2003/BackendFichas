<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventanilla;
use App\Models\Dominio;

class VentanillaServicioController extends Controller
{
    /**
     * Asigna servicios a una ventanilla (reemplaza todos los actuales).
     * POST /api/ventanillas/{id}/servicios
     * Body: { "servicios": [1,2,3] }
     */
    public function asignarServicios(Request $request, $id)
    {
        $request->validate([
            'servicios' => 'required|array',
            'servicios.*' => 'string'
        ]);
        $nombres = $request->servicios;
        // Buscar los dominios por nombre y grupo tipo_servicio
        $dominios = \App\Models\Dominio::whereHas('dominioGrupo', function($q) {
            $q->where('nombre', 'tipo_servicio');
        })->whereIn('nombre', $nombres)->get();
        if (count($dominios) !== count($nombres)) {
            return response()->json([
                'message' => 'Uno o más servicios no son válidos',
                'servicios_enviados' => $nombres,
                'servicios_validos' => $dominios->pluck('nombre')
            ], 422);
        }
        $ventanilla = Ventanilla::findOrFail($id);
        $ventanilla->tiposServicio()->sync($dominios->pluck('dominio_id'));
        // Volver a cargar la relación y evitar ambigüedad en PostgreSQL
        $servicios = $ventanilla->tiposServicio()
            ->select('dominios.dominio_id', 'dominios.nombre')
            ->get();
        return response()->json([
            'message' => 'Servicios actualizados',
            'ventanilla_id' => $ventanilla->ventanilla_id,
            'servicios' => $servicios
        ]);
    }
}
