<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sesion;
use App\Models\Seguimiento;
use Carbon\Carbon;

class VentanillaDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $ventanillaId = $request->user()->fk_ventanilla_id;
        $fecha = $request->input('fecha', now()->toDateString());

        $sesion = Sesion::where('fk_ventanilla_id', $ventanillaId)
            ->whereDate('fecha', $fecha)
            ->latest()
            ->first();

        $finalizadas = Seguimiento::where('fk_ventanilla_id', $ventanillaId)
            ->whereHas('dominioEstado', function($q) {
                $q->where('nombre', 'finalizado');
            })
            ->whereDate('fecha', $fecha)
            ->count();

        $ausentes = Seguimiento::where('fk_ventanilla_id', $ventanillaId)
            ->whereHas('dominioEstado', function($q) {
                $q->where('nombre', 'ausente');
            })
            ->whereDate('fecha', $fecha)
            ->count();

        $now = Carbon::now();
        $puedeCerrar = $sesion && $sesion->estado === 'activa' && $now->gte($now->copy()->setTime(16,0));
        $puedeIniciar = $sesion ? $sesion->estado === 'cerrada' : true;

        return response()->json([
            'finalizadas' => $finalizadas,
            'ausentes' => $ausentes,
            'total' => $finalizadas + $ausentes,
            'sesion' => [
                'estado' => $sesion->estado ?? 'sin_sesion',
                'puede_iniciar' => $puedeIniciar,
                'puede_cerrar' => $puedeCerrar,
            ],
            'usuario' => [
                'nombre' => $request->user()->nombre_completo,
                'correo' => $request->user()->correo_electronico,
            ]
        ]);
    }
}
