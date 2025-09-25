<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asignacion;

class AsignacionController extends Controller
{
    public function index(Request $request)
    {
    $query = Asignacion::with(['usuario', 'organizacion', 'sucursal', 'ventanilla']);
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('organizacion_id')) {
            $query->where('fk_organizacion_id', $request->organizacion_id);
        }
        if ($request->has('sucursal_id')) {
            $query->where('fk_sucursal_id', $request->sucursal_id);
        }
        if ($request->has('ventanilla_id')) {
            $query->where('fk_ventanilla_id', $request->ventanilla_id);
        }
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }
        return $query->get();
    }

    public function show($id)
    {
        return Asignacion::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'fk_organizacion_id' => 'required|exists:organizaciones,organizacion_id',
            'fk_sucursal_id' => 'required|exists:sucursales,sucursal_id',
            'fk_ventanilla_id' => 'nullable|exists:ventanillas,ventanilla_id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'required|boolean',
        ]);
        $asignacion = Asignacion::create($validated);
        return response()->json($asignacion, 201);
    }

    public function update(Request $request, $id)
    {
        $asignacion = Asignacion::findOrFail($id);
        $validated = $request->validate([
            'fk_usuario_id' => 'sometimes|exists:usuarios,usuario_id',
            'fk_organizacion_id' => 'sometimes|exists:organizaciones,organizacion_id',
            'fk_sucursal_id' => 'sometimes|exists:sucursales,sucursal_id',
            'fk_ventanilla_id' => 'nullable|exists:ventanillas,ventanilla_id',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'sometimes|boolean',
        ]);
        $asignacion->update($validated);
        return response()->json($asignacion);
    }

    public function destroy($id)
    {
        Asignacion::destroy($id);
        return response()->json(null, 204);
    }
}
