<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventanilla;
use App\Services\VentanillaService;

class VentanillaController extends Controller
{
    /**
     * Devuelve los servicios que atiende una ventanilla.
     * GET /api/ventanillas/{id}/servicios
     */
    public function servicios($id)
    {
        $ventanilla = Ventanilla::with('tiposServicio')->findOrFail($id);
        // Solo devolver nombre y dominio_id
        $servicios = $ventanilla->tiposServicio->map(function($servicio) {
            return [
                'dominio_id' => $servicio->dominio_id,
                'nombre' => $servicio->nombre
            ];
        });
        return response()->json([
            'ventanilla_id' => $ventanilla->ventanilla_id,
            'numero' => $ventanilla->numero,
            'servicios' => $servicios
        ]);
    }
    public function index(Request $request) {
        $query = Ventanilla::with(['sucursal.organizacion']);
        if ($request->has('sucursal_id')) {
            $query->where('fk_sucursal_id', $request->sucursal_id);
        }
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
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
        $ventanilla = Ventanilla::find($id);
        if (!$ventanilla) {
            return response()->json(['message' => 'No se encontró la ventanilla especificada.'], 404);
        }
        $ventanilla->estado = Ventanilla::ESTADO_CERRADA;
        $ventanilla->save();

        // Verificar si todas las ventanillas de la sucursal están cerradas
        $sucursalId = $ventanilla->fk_sucursal_id;
        $abiertas = \App\Models\Ventanilla::where('fk_sucursal_id', $sucursalId)
            ->where('estado', \App\Models\Ventanilla::ESTADO_ABIERTA)
            ->count();

        if ($abiertas === 0) {
            // Cerrar la sesión activa de la sucursal para hoy
            $dominioActiva = \App\Models\Dominio::where('nombre', 'activa')->first();
            $dominioCerrada = \App\Models\Dominio::where('nombre', 'cerrada')->first();
            $sesion = \App\Models\Sesion::where('fk_sucursal_id', $sucursalId)
                ->whereDate('fecha', now()->toDateString())
                ->where('fk_dominio_estado_id', $dominioActiva ? $dominioActiva->dominio_id : null)
                ->orderByDesc('fecha')
                ->first();
            if ($sesion && $dominioCerrada) {
                $sesion->fk_dominio_estado_id = $dominioCerrada->dominio_id;
                $sesion->hora_cierre = now();
                $sesion->save();
            } elseif (!$sesion) {
                return response()->json(['message' => 'No se encontró una sesión activa para cerrar en la sucursal.'], 404);
            } elseif (!$dominioCerrada) {
                return response()->json(['message' => 'Error interno: No se encontró el dominio para el estado "cerrada". Contacte a soporte.'], 500);
            }
        }

        return response()->json(['message' => 'Ventanilla cerrada correctamente.', 'ventanilla' => $ventanilla]);
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
