<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DominioGrupo;

class DominioGrupoController extends Controller
{
    public function index() { return DominioGrupo::all(); }
    public function show($id) { return DominioGrupo::findOrFail($id); }
    public function store(Request $request) { $dominioGrupo = DominioGrupo::create($request->all()); return response()->json($dominioGrupo, 201); }
    public function update(Request $request, $id) { $dominioGrupo = DominioGrupo::findOrFail($id); $dominioGrupo->update($request->all()); return response()->json($dominioGrupo); }
    public function destroy($id) { DominioGrupo::destroy($id); return response()->json(null, 204); }
}
