<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seguimiento;
use App\Services\SeguimientoService;

class SeguimientoController extends Controller
{
    public function index(Request $request) {
        $query = Seguimiento::query();
        if ($request->has('ventanilla_id')) {
            $query->where('fk_ventanilla_id', $request->ventanilla_id);
        }
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }
        if ($request->has('estado')) {
            // Buscar el dominio por nombre y filtrar por su id
            $estadoNombre = $request->estado;
            $dominio = \App\Models\Dominio::where('nombre', $estadoNombre)->first();
            if ($dominio) {
                $query->where('fk_dominio_estado_id', $dominio->dominio_id);
            } else {
                // Si no existe el estado, devolver vacío
                return response()->json([]);
            }
        }
        if ($request->has('seguimiento_id')) {
            $query->where('seguimiento_id', $request->seguimiento_id);
        }
        return $query->get();
    }
    public function show($id) { return Seguimiento::findOrFail($id); }
    public function store(\App\Http\Requests\StoreSeguimientoRequest $request, SeguimientoService $seguimientoService) {
        $seguimiento = $seguimientoService->crearSeguimiento($request->validated());
        return response()->json($seguimiento, 201);
    }
    public function update(\App\Http\Requests\UpdateSeguimientoRequest $request, $id, SeguimientoService $seguimientoService) {
        $seguimiento = Seguimiento::findOrFail($id);
        $seguimiento = $seguimientoService->actualizarSeguimiento($seguimiento, $request->validated());
        return response()->json($seguimiento);
    }
    public function destroy($id) { Seguimiento::destroy($id); return response()->json(null, 204); }
}
