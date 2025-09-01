<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventanilla;

class VentanillaController extends Controller
{
    public function index() { return Ventanilla::all(); }
    public function show($id) { return Ventanilla::findOrFail($id); }
    public function store(Request $request) { $ventanilla = Ventanilla::create($request->all()); return response()->json($ventanilla, 201); }
    public function update(Request $request, $id) { $ventanilla = Ventanilla::findOrFail($id); $ventanilla->update($request->all()); return response()->json($ventanilla); }
    public function destroy($id) { Ventanilla::destroy($id); return response()->json(null, 204); }
}
