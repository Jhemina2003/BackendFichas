<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Services\FichaService;

class FichaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ficha::query();
        if ($request->has('sesion_id')) {
            $query->where('fk_sesion_id', $request->sesion_id);
        }
        if ($request->has('tipo_ficha_id')) {
            $query->where('fk_tipo_ficha_id', $request->tipo_ficha_id);
        }
        if ($request->has('tipo_servicio_id')) {
            $query->where('fk_tipo_servicio_id', $request->tipo_servicio_id);
        }
        if ($request->has('prioridad_ficha_id')) {
            $query->where('fk_prioridad_ficha_id', $request->prioridad_ficha_id);
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha_registro', $request->fecha);
        }
        if ($request->has('estado')) {
            $estadoNombre = $request->estado;
            $dominio = \App\Models\Dominio::where('nombre', $estadoNombre)->first();
            if ($dominio) {
                $query->where('fk_dominio_tipo_id', $dominio->dominio_id);
            } else {
                return response()->json([]);
            }
        }
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('sucursal_id')) {
            $query->whereHas('sesion', function($q) use ($request) {
                $q->where('fk_sucursal_id', $request->sucursal_id);
            });
        }
        return $query->get();
    }

    public function store(\App\Http\Requests\StoreFichaRequest $request, FichaService $fichaService)
    {
        $ficha = $fichaService->crearFicha($request->validated());
        return response()->json($ficha, 201);
    }

    public function show($id)
    {
        return Ficha::findOrFail($id);
    }


    public function update(\App\Http\Requests\UpdateFichaRequest $request, $id, FichaService $fichaService)
    {
        $ficha = Ficha::findOrFail($id);
        $ficha = $fichaService->actualizarFicha($ficha, $request->validated());
        return response()->json($ficha);
    }

    public function destroy($id)
    {
        Ficha::destroy($id);
        return response()->json(null, 204);
    }
}
