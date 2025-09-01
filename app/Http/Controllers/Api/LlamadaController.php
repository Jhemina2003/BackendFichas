<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Llamada;

class LlamadaController extends Controller
{
    public function index()
    {
        return Llamada::all();
    }

    public function show($id)
    {
        return Llamada::findOrFail($id);
    }

    public function store(Request $request)
    {
        $llamada = Llamada::create($request->all());
        return response()->json($llamada, 201);
    }

    public function update(Request $request, $id)
    {
        $llamada = Llamada::findOrFail($id);
        $llamada->update($request->all());
        return response()->json($llamada);
    }

    public function destroy($id)
    {
        Llamada::destroy($id);
        return response()->json(null, 204);
    }
}
