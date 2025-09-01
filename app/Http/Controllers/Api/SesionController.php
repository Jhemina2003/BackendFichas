<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sesion;

class SesionController extends Controller
{
    public function index() { return Sesion::all(); }
    public function show($id) { return Sesion::findOrFail($id); }
    public function store(Request $request) { $sesion = Sesion::create($request->all()); return response()->json($sesion, 201); }
    public function update(Request $request, $id) { $sesion = Sesion::findOrFail($id); $sesion->update($request->all()); return response()->json($sesion); }
    public function destroy($id) { Sesion::destroy($id); return response()->json(null, 204); }
}
