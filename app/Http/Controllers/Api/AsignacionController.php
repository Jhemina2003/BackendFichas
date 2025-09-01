<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asignacion;

class AsignacionController extends Controller
{
    public function index()
    {
        return Asignacion::all();
    }

    public function show($id)
    {
        return Asignacion::findOrFail($id);
    }

    public function store(Request $request)
    {
        $asignacion = Asignacion::create($request->all());
        return response()->json($asignacion, 201);
    }

    public function update(Request $request, $id)
    {
        $asignacion = Asignacion::findOrFail($id);
        $asignacion->update($request->all());
        return response()->json($asignacion);
    }

    public function destroy($id)
    {
        Asignacion::destroy($id);
        return response()->json(null, 204);
    }
}
