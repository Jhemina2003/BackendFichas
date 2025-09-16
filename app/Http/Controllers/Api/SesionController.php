<?php


namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSesionRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sesion;
use App\Services\SesionService;

class SesionController extends Controller
{
    public function index(Request $request) {
        $query = Sesion::query();
        if ($request->has('sucursal_id')) {
            $query->where('fk_sucursal_id', $request->sucursal_id);
        }
        if ($request->has('estado')) {
            // Buscar el dominio por nombre y filtrar por su id
            $estadoNombre = $request->estado;
            $dominio = \App\Models\Dominio::where('nombre', $estadoNombre)->first();
            if ($dominio) {
                $query->where('fk_dominio_estado_id', $dominio->dominio_id);
            } else {
                return response()->json([]);
            }
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }
        return $query->get();
    }
    public function show($id) { return Sesion::findOrFail($id); }
    public function store(\App\Http\Requests\StoreSesionRequest $request, SesionService $sesionService) {
        $sesion = $sesionService->crearSesion($request->validated());
        return response()->json($sesion, 201);
    }
    public function update(\App\Http\Requests\UpdateSesionRequest $request, $id, SesionService $sesionService) {
        $sesion = Sesion::findOrFail($id);
        $sesion = $sesionService->actualizarSesion($sesion, $request->validated());
        return response()->json($sesion);
    }
    public function destroy($id) { Sesion::destroy($id); return response()->json(null, 204); }
}
