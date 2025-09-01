<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RolUsuario;

class RolUsuarioController extends Controller
{
    public function index() { return RolUsuario::all(); }
    public function show($id) { return RolUsuario::findOrFail($id); }
    public function store(Request $request) { $rolUsuario = RolUsuario::create($request->all()); return response()->json($rolUsuario, 201); }
    public function update(Request $request, $id) { $rolUsuario = RolUsuario::findOrFail($id); $rolUsuario->update($request->all()); return response()->json($rolUsuario); }
    public function destroy($id) { RolUsuario::destroy($id); return response()->json(null, 204); }
}
