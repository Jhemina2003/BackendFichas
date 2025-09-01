<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seguimiento;

class SeguimientoController extends Controller
{
    public function index() { return Seguimiento::all(); }
    public function show($id) { return Seguimiento::findOrFail($id); }
    public function store(Request $request) { $seguimiento = Seguimiento::create($request->all()); return response()->json($seguimiento, 201); }
    public function update(Request $request, $id) { $seguimiento = Seguimiento::findOrFail($id); $seguimiento->update($request->all()); return response()->json($seguimiento); }
    public function destroy($id) { Seguimiento::destroy($id); return response()->json(null, 204); }
}
