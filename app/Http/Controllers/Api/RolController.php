<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rol;

class RolController extends Controller
{
    public function index() { return Rol::all(); }
    public function show($id) { return Rol::findOrFail($id); }
    public function store(Request $request) { $rol = Rol::create($request->all()); return response()->json($rol, 201); }
    public function update(Request $request, $id) { $rol = Rol::findOrFail($id); $rol->update($request->all()); return response()->json($rol); }
    public function destroy($id) { Rol::destroy($id); return response()->json(null, 204); }
}
