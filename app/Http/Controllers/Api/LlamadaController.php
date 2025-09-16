<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Llamada;
use App\Services\LlamadaService;

class LlamadaController extends Controller
{
    public function index(Request $request)
    {
        $query = Llamada::query();
        if ($request->has('ficha_id')) {
            $query->where('fk_ficha_id', $request->ficha_id);
        }
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('ventanilla_id')) {
            $query->where('fk_ventanilla_id', $request->ventanilla_id);
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }
        return $query->get();
    }

    public function show($id)
    {
        return Llamada::findOrFail($id);
    }

    public function store(\App\Http\Requests\StoreLlamadaRequest $request, LlamadaService $llamadaService)
    {
        $llamada = $llamadaService->crearLlamada($request->validated());
        return response()->json($llamada, 201);
    }

    public function update(\App\Http\Requests\UpdateLlamadaRequest $request, $id, LlamadaService $llamadaService)
    {
        $llamada = Llamada::findOrFail($id);
        $llamada = $llamadaService->actualizarLlamada($llamada, $request->validated());
        return response()->json($llamada);
    }

    public function destroy($id)
    {
        Llamada::destroy($id);
        return response()->json(null, 204);
    }
}
