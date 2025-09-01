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
