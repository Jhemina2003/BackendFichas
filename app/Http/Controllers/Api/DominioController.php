<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dominio;

class DominioController extends Controller
{
    public function index() { return Dominio::all(); }
    public function show($id) { return Dominio::findOrFail($id); }
    public function store(Request $request) { $dominio = Dominio::create($request->all()); return response()->json($dominio, 201); }
    public function update(Request $request, $id) { $dominio = Dominio::findOrFail($id); $dominio->update($request->all()); return response()->json($dominio); }
    public function destroy($id) { Dominio::destroy($id); return response()->json(null, 204); }
}
