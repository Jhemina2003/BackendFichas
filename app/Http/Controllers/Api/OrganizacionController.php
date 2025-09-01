<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organizacion;

class OrganizacionController extends Controller
{
    public function index() { return Organizacion::all(); }
    public function show($id) { return Organizacion::findOrFail($id); }
    public function store(Request $request) { $organizacion = Organizacion::create($request->all()); return response()->json($organizacion, 201); }
    public function update(Request $request, $id) { $organizacion = Organizacion::findOrFail($id); $organizacion->update($request->all()); return response()->json($organizacion); }
    public function destroy($id) { Organizacion::destroy($id); return response()->json(null, 204); }
}
