<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Horario;

class HorarioController extends Controller
{
    public function index() { return Horario::all(); }
    public function show($id) { return Horario::findOrFail($id); }
    public function store(Request $request) { $horario = Horario::create($request->all()); return response()->json($horario, 201); }
    public function update(Request $request, $id) { $horario = Horario::findOrFail($id); $horario->update($request->all()); return response()->json($horario); }
    public function destroy($id) { Horario::destroy($id); return response()->json(null, 204); }
}
