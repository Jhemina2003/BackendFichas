<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventanilla;
use App\Services\VentanillaService;

class VentanillaController extends Controller
{
    public function index(Request $request) {
        $query = Ventanilla::query();
        if ($request->has('sucursal_id')) {
            $query->where('fk_sucursal_id', $request->sucursal_id);
        }
        if ($request->has('bloqueado')) {
            $query->where('bloqueado', $request->bloqueado);
        }
        if ($request->has('numero')) {
            $query->where('numero', $request->numero);
        }
        return $query->get();
    }
    public function show($id) { return Ventanilla::findOrFail($id); }
    public function store(\App\Http\Requests\StoreVentanillaRequest $request, VentanillaService $ventanillaService) {
        $ventanilla = $ventanillaService->crearVentanilla($request->validated());
        return response()->json($ventanilla, 201);
    }

    /**
     * Cierra una ventanilla (cambia estado a 'cerrada').
     * POST /api/ventanillas/{id}/cerrar
     */
    public function cerrar($id)
    {
        $ventanilla = Ventanilla::findOrFail($id);
        $ventanilla->estado = Ventanilla::ESTADO_CERRADA;
        $ventanilla->save();
        return response()->json(['message' => 'Ventanilla cerrada', 'ventanilla' => $ventanilla]);
    }

    /**
     * Abre una ventanilla (cambia estado a 'abierta').
     * POST /api/ventanillas/{id}/abrir
     */
    public function abrir($id)
    {
        $ventanilla = Ventanilla::findOrFail($id);
        $ventanilla->estado = Ventanilla::ESTADO_ABIERTA;
        $ventanilla->save();
        return response()->json(['message' => 'Ventanilla abierta', 'ventanilla' => $ventanilla]);
    }

    /**
     * Verifica si todas las ventanillas están cerradas.
     * GET /api/ventanillas/todas-cerradas
     */
    public function todasCerradas()
    {
        $abiertas = Ventanilla::where('estado', Ventanilla::ESTADO_ABIERTA)->count();
        return response()->json(['todas_cerradas' => $abiertas === 0]);
    }
    public function update(\App\Http\Requests\UpdateVentanillaRequest $request, $id, VentanillaService $ventanillaService) {
        $ventanilla = Ventanilla::findOrFail($id);
        $ventanilla = $ventanillaService->actualizarVentanilla($ventanilla, $request->validated());
        return response()->json($ventanilla);
    }
    public function destroy($id) { Ventanilla::destroy($id); return response()->json(null, 204); }
}
