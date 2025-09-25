<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seguimiento;
use App\Services\SeguimientoService;

class SeguimientoController extends Controller
{
    /**
     * Reasigna una ficha a otra ventanilla, insertándola como primera en la cola de espera.
     * Reglas: no permite reasignar a ventanilla cerrada/bloqueada, registra seguimiento, cambia estado a 'en_espera'.
     */
    public function reasignarFicha(Request $request, $fichaId, SeguimientoService $seguimientoService)
    {
        $request->validate([
            'fk_ventanilla_destino_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'justificativo' => 'required|string|min:5',
        ]);
        $ficha = $this->findFichaByIdOrNumero($fichaId);
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'en_atencion') {
            return response()->json(['message' => 'Solo se puede reasignar una ficha en estado "en_atencion".'], 409);
        }
        $ventanillaDestino = \App\Models\Ventanilla::findOrFail($request->fk_ventanilla_destino_id);
        // Validar ventanilla destino abierta
        if ($ventanillaDestino->estado === 'cerrada') {
            return response()->json(['message' => 'No se puede reasignar a una ventanilla cerrada.'], 409);
        }
        $ventanillaOrigen = $ficha->seguimientos()->latest('created_at')->first()?->ventanilla;
        // Registrar seguimiento de reasignación
        $seguimiento = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $ventanillaDestino->ventanilla_id,
            'fk_usuario_id' => $request->fk_usuario_id,
            'estado' => 'reasignado',
            'fk_dominio_accion_id' => \App\Models\Dominio::where('nombre', 'reasignado')->first()?->dominio_id,
            'fecha' => now(),
            'observacion' => 'Reasignación de ventanilla '.($ventanillaOrigen ? $ventanillaOrigen->numero : 'N/A').
                ' a '.$ventanillaDestino->numero.'. Motivo: '.$request->justificativo
        ]);
        // Cambiar estado de la ficha a 'en_espera' y actualizar ventanilla si corresponde
        // (Opcional: si la ficha tiene campo fk_ventanilla_id, actualizarlo)
        // Insertar como primera en la cola: crear seguimiento 'en_espera' con fecha = now() - 1 segundo
        $dominioEspera = \App\Models\Dominio::where('nombre', 'en_espera')->first();
        $seguimientoEspera = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $ventanillaDestino->ventanilla_id,
            'fk_usuario_id' => $request->fk_usuario_id,
            'estado' => 'en_espera',
            'fk_dominio_accion_id' => $dominioEspera?->dominio_id,
            'fecha' => now()->subSecond(),
            'observacion' => 'Ficha en espera tras reasignación.'
        ]);
        return response()->json([
            'seguimiento_reasignacion' => $seguimiento,
            'seguimiento_espera' => $seguimientoEspera,
            'message' => 'Ficha reasignada correctamente a la ventanilla '.$ventanillaDestino->numero.' y puesta como primera en espera.'
        ], 201);
    }
// ...existing code...
    /**
     * Busca una ficha por ID numérico o por número formateado (con prefijo).
     */
    private function findFichaByIdOrNumero($fichaParam)
    {
        if (is_numeric($fichaParam)) {
            return \App\Models\Ficha::findOrFail($fichaParam);
        }
        // Buscar por número formateado
        $fichas = \App\Models\Ficha::all();
        foreach ($fichas as $ficha) {
            if ($ficha->numero_formateado === $fichaParam) {
                return $ficha;
            }
        }
        abort(404, 'Ficha no encontrada por número formateado');
    }
    // Marcar ficha como ausente
    public function marcarAusente(Request $request, $fichaId, SeguimientoService $seguimientoService)
    {
        $request->validate([
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'observacion' => 'nullable|string',
        ]);
    $ficha = $this->findFichaByIdOrNumero($fichaId);
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'llamado') {
            return response()->json(['message' => 'Solo se puede marcar como ausente una ficha en estado "llamado".'], 409);
        }
        try {
            $dominio = \App\Models\Dominio::where('nombre', 'ausente')->first();
            if (!$dominio) {
                return response()->json(['message' => "No existe un dominio con nombre 'ausente' para el estado de la ficha."], 422);
            }
            $seguimiento = $seguimientoService->crearSeguimiento([
                'fk_ficha_id' => $fichaId,
                'fk_ventanilla_id' => $request->fk_ventanilla_id,
                'fk_usuario_id' => $request->fk_usuario_id,
                'estado' => 'ausente',
                'fk_dominio_accion_id' => $dominio->dominio_id,
                'fecha' => now(),
                'observacion' => $request->observacion ?? 'Ficha marcada como ausente.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json($seguimiento, 201);
    }
    // Marcar ficha como en_atencion
    public function marcarEnAtencion(Request $request, $fichaId, SeguimientoService $seguimientoService)
    {
        $request->validate([
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
        ]);
    $ficha = $this->findFichaByIdOrNumero($fichaId);
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'llamado') {
            return response()->json(['message' => 'La ficha no está en estado "llamado". Puede que ya esté siendo atendida o finalizada.'], 409);
        }
        $seguimiento = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $fichaId,
            'fk_ventanilla_id' => $request->fk_ventanilla_id,
            'fk_usuario_id' => $request->fk_usuario_id,
            'estado' => 'en_atencion',
            'fk_dominio_accion_id' => null,
            'fecha' => now(),
            'observacion' => 'Ficha en atención.'
        ]);
        return response()->json($seguimiento, 201);
    }

    // Marcar ficha como finalizada
    public function marcarFinalizada(Request $request, $fichaId, SeguimientoService $seguimientoService)
    {
        $request->validate([
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
        ]);
        $ficha = $this->findFichaByIdOrNumero($fichaId);
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'en_atencion') {
            return response()->json(['message' => 'Solo se puede finalizar una ficha en estado "en_atencion".'], 409);
        }
        if ($estadoActual === 'cancelado') {
            return response()->json(['message' => 'No se puede finalizar una ficha que ya está cancelada.'], 409);
        }
        try {
            // Buscar el dominio correspondiente
            $dominio = \App\Models\Dominio::where('nombre', 'finalizado')->first();
            if (!$dominio) {
                return response()->json(['message' => "No existe un dominio con nombre 'finalizado' para el estado de la ficha."], 422);
            }
            $seguimiento = $seguimientoService->crearSeguimiento([
                'fk_ficha_id' => $fichaId,
                'fk_ventanilla_id' => $request->fk_ventanilla_id,
                'fk_usuario_id' => $request->fk_usuario_id,
                'estado' => 'finalizado',
                'fk_dominio_accion_id' => $dominio->dominio_id,
                'fecha' => now(),
                'observacion' => 'Ficha finalizada.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json($seguimiento, 201);
    }

    // Marcar ficha como cancelada (requiere observación)
    public function marcarCancelada(Request $request, $fichaId, SeguimientoService $seguimientoService)
    {
        $request->validate([
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'justificativo' => 'required|string|min:5',
        ]);
    $ficha = $this->findFichaByIdOrNumero($fichaId);
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual === 'finalizado') {
            return response()->json(['message' => 'No se puede cancelar una ficha que ya está finalizada.'], 409);
        }
        try {
            // Buscar el dominio correspondiente
            $dominio = \App\Models\Dominio::where('nombre', 'cancelado')->first();
            if (!$dominio) {
                return response()->json(['message' => "No existe un dominio con nombre 'cancelado' para el estado de la ficha."], 422);
            }
            $seguimiento = $seguimientoService->crearSeguimiento([
                'fk_ficha_id' => $fichaId,
                'fk_ventanilla_id' => $request->fk_ventanilla_id,
                'fk_usuario_id' => $request->fk_usuario_id,
                'estado' => 'cancelado',
                'fk_dominio_accion_id' => $dominio->dominio_id,
                'fecha' => now(),
                'observacion' => $request->justificativo
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json($seguimiento, 201);
    }
    public function index(Request $request) {
        $query = Seguimiento::query();
        if ($request->has('ventanilla_id')) {
            $query->where('fk_ventanilla_id', $request->ventanilla_id);
        }
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }
        if ($request->has('estado')) {
            // Buscar el dominio por nombre y filtrar por su id
            $estadoNombre = $request->estado;
            $dominio = \App\Models\Dominio::where('nombre', $estadoNombre)->first();
            if ($dominio) {
                $query->where('fk_dominio_estado_id', $dominio->dominio_id);
            } else {
                // Si no existe el estado, devolver vacío
                return response()->json([]);
            }
        }
        if ($request->has('seguimiento_id')) {
            $query->where('seguimiento_id', $request->seguimiento_id);
        }
        return $query->get();
    }
    public function show($id) { return Seguimiento::findOrFail($id); }
    
    public function store(\App\Http\Requests\StoreSeguimientoRequest $request, SeguimientoService $seguimientoService) {
        $seguimiento = $seguimientoService->crearSeguimiento($request->validated());
        return response()->json($seguimiento, 201);
    }
    public function update(\App\Http\Requests\UpdateSeguimientoRequest $request, $id, SeguimientoService $seguimientoService) {
        $seguimiento = Seguimiento::findOrFail($id);
        $seguimiento = $seguimientoService->actualizarSeguimiento($seguimiento, $request->validated());
        return response()->json($seguimiento);
    }
    public function destroy($id) { Seguimiento::destroy($id); return response()->json(null, 204); }


    /**
     * Devuelve el historial completo de seguimientos de una ficha, con filtros opcionales
     */
    public function historial(Request $request, $fichaId)
    {
        $query = \App\Models\Seguimiento::with(['dominioEstado', 'usuario', 'ventanilla'])
            ->where('fk_ficha_id', $fichaId);

        if ($request->filled('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->filled('ventanilla_id')) {
            $query->where('fk_ventanilla_id', $request->ventanilla_id);
        }
        if ($request->filled('estado')) {
            $query->whereHas('dominioEstado', function($q) use ($request) {
                $q->where('nombre', $request->estado);
            });
        }
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->fecha_fin);
        }
        return $query->orderBy('fecha', 'asc')->get();
    }
}
