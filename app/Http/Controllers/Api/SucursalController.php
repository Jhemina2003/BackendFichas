<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;

class SucursalController extends Controller
{
    public function index() { return Sucursal::all(); }
    public function show($id) { return Sucursal::findOrFail($id); }
    public function store(Request $request) { $sucursal = Sucursal::create($request->all()); return response()->json($sucursal, 201); }
    public function update(Request $request, $id) { $sucursal = Sucursal::findOrFail($id); $sucursal->update($request->all()); return response()->json($sucursal); }
    public function destroy($id) { Sucursal::destroy($id); return response()->json(null, 204); }
}
