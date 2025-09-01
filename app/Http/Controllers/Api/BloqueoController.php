<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bloqueo;

class BloqueoController extends Controller
{
    public function index() { return Bloqueo::all(); }
    public function show($id) { return Bloqueo::findOrFail($id); }
    public function store(Request $request) { $bloqueo = Bloqueo::create($request->all()); return response()->json($bloqueo, 201); }
    public function update(Request $request, $id) { $bloqueo = Bloqueo::findOrFail($id); $bloqueo->update($request->all()); return response()->json($bloqueo); }
    public function destroy($id) { Bloqueo::destroy($id); return response()->json(null, 204); }
}
