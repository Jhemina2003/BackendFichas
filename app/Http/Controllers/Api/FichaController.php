<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ficha;

class FichaController extends Controller
{
    public function index()
    {
        return Ficha::all();
    }

    public function show($id)
    {
        return Ficha::findOrFail($id);
    }

    public function store(Request $request)
    {
        // Validar que el dominio de tipo de ficha corresponda al grupo correcto
        $tipoFichaGrupo = \App\Models\DominioGrupo::where('nombre', 'tipo_ficha')->first();
        $dominioTipo = \App\Models\Dominio::where('dominio_id', $request->fk_dominio_tipo_id)
            ->where('fk_dominio_grupo_id', $tipoFichaGrupo ? $tipoFichaGrupo->dominio_grupo_id : null)
            ->first();

        if (!$dominioTipo) {
            return response()->json(['error' => 'El dominio de tipo de ficha no es válido para el grupo tipo_ficha'], 422);
        }

        $ficha = Ficha::create($request->all());
        return response()->json($ficha, 201);
    }

    public function update(Request $request, $id)
    {
        $ficha = Ficha::findOrFail($id);
        $ficha->update($request->all());
        return response()->json($ficha);
    }

    public function destroy($id)
    {
        Ficha::destroy($id);
        return response()->json(null, 204);
    }
}
