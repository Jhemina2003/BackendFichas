<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seguimiento;
use App\Services\SeguimientoService;

class SeguimientoController extends Controller
{
    // Rellamar ficha (incrementa cantidad_llamadas, estado sigue en 'llamado')
    public function rellamar(Request $request, SeguimientoService $seguimientoService)
    {
        $usuario = auth()->user();
        
        // Validar que la ventanilla tenga una sesión activa
        $sesionActiva = \App\Models\SesionVentanilla::where('fk_ventanilla_id', $usuario->fk_ventanilla_id)
            ->where('fk_usuario_id', $usuario->usuario_id)
            ->where('estado', 'activa')
            ->whereNull('hora_cierre')
            ->first();
            
        if (!$sesionActiva) {
            return response()->json(['message' => 'Debe iniciar sesión en la ventanilla antes de realizar acciones'], 403);
        }
        
        // Buscar la última ficha llamada por esta ventanilla
        $ficha = $this->getFichaActualVentanilla($usuario);
        if (!$ficha) {
            return response()->json(['message' => 'No hay ninguna ficha activa para esta ventanilla.'], 404);
        }
        
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'llamado') {
            return response()->json(['message' => 'Solo se puede rellamar una ficha en estado "llamado".'], 409);
        }
        
        // Incrementar cantidad_llamadas
        $ficha->cantidad_llamadas++;
        $ficha->save();
        
        // Registrar seguimiento de rellamada
        $dominioLlamado = \App\Models\Dominio::where('nombre', 'llamado')->first();
        $seguimiento = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
            'fk_usuario_id' => $usuario->usuario_id,
            'estado' => 'llamado',
            'fk_dominio_accion_id' => $dominioLlamado?->dominio_id,
            'fecha' => now(),
            'observacion' => 'Ficha rellamada. Total llamadas: ' . $ficha->cantidad_llamadas
        ]);
        return response()->json($seguimiento, 201);
    }

    // Observación obligatoria y finaliza
    public function observacion(Request $request, SeguimientoService $seguimientoService)
    {
        $usuario = auth()->user();
        $request->validate([
            'observacion' => 'required|string|min:5',
        ]);
        
        $ficha = $this->getFichaActualVentanilla($usuario);
        if (!$ficha) {
            return response()->json(['message' => 'No hay ninguna ficha activa para esta ventanilla.'], 404);
        }
        
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'en_atencion') {
            return response()->json(['message' => 'Solo se puede agregar observación a una ficha en atención.'], 409);
        }
        
        $dominioFinalizado = \App\Models\Dominio::where('nombre', 'finalizado')->first();
        $seguimiento = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
            'fk_usuario_id' => $usuario->usuario_id,
            'estado' => 'finalizado',
            'fk_dominio_accion_id' => $dominioFinalizado?->dominio_id,
            'fecha' => now(),
            'observacion' => $request->observacion
        ]);
        return response()->json($seguimiento, 201);
    }

    // Redirigir ficha (reasignado y finalizado)
    public function redirigir(Request $request, SeguimientoService $seguimientoService)
    {
        $usuario = auth()->user();
        $request->validate([
            'justificativo' => 'required|string|min:5',
        ]);
        
        $ficha = $this->getFichaActualVentanilla($usuario);
        if (!$ficha) {
            return response()->json(['message' => 'No hay ninguna ficha activa para esta ventanilla.'], 404);
        }
        
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'en_atencion') {
            return response()->json(['message' => 'Solo se puede redirigir una ficha en atención.'], 409);
        }
        
        $dominioReasignado = \App\Models\Dominio::where('nombre', 'reasignado')->first();
        $seguimiento = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
            'fk_usuario_id' => $usuario->usuario_id,
            'estado' => 'reasignado',
            'fk_dominio_accion_id' => $dominioReasignado?->dominio_id,
            'fecha' => now(),
            'observacion' => 'Ficha redirigida. Motivo: ' . $request->justificativo
        ]);
        // Finalizar
        $dominioFinalizado = \App\Models\Dominio::where('nombre', 'finalizado')->first();
        $seguimientoFinal = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
            'fk_usuario_id' => $usuario->usuario_id,
            'estado' => 'finalizado',
            'fk_dominio_accion_id' => $dominioFinalizado?->dominio_id,
            'fecha' => now(),
            'observacion' => 'Ficha finalizada tras redirección.'
        ]);
        return response()->json([
            'seguimiento_redirigir' => $seguimiento,
            'seguimiento_final' => $seguimientoFinal
        ], 201);
    }
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
        $ultimoEnAtencion = $ficha->seguimientos()->whereHas('dominioEstado', function($q){ $q->where('nombre', 'en_atencion'); })->latest('fecha')->first();
        if ($estadoActual !== 'en_atencion') {
            return response()->json(['message' => 'Solo se puede redirigir una ficha que está siendo atendida actualmente.'], 409);
        }
        if (!$ultimoEnAtencion || $ultimoEnAtencion->fk_ventanilla_id != $request->fk_ventanilla_id) {
            return response()->json(['message' => 'Solo la ventanilla que está atendiendo la ficha puede realizar la redirección.'], 409);
        }
        if (empty($request->justificativo)) {
            return response()->json(['message' => 'Debes ingresar una justificación para redirigir la ficha.'], 422);
        }
        $ventanillaDestino = \App\Models\Ventanilla::findOrFail($request->fk_ventanilla_destino_id);
        // Validar ventanilla destino abierta
        if ($ventanillaDestino->estado === 'cerrada') {
            return response()->json(['message' => 'No se puede reasignar a una ventanilla cerrada.'], 409);
        }
        $ventanillaOrigen = $ficha->seguimientos()->latest('created_at')->first()?->ventanilla;

        // INICIO TRANSACCIÓN
        try {
            \DB::beginTransaction();

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

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error al reasignar la ficha: ' . $e->getMessage()
            ], 500);
        }
        // FIN TRANSACCIÓN
        return response()->json([
            'seguimiento_reasignacion' => $seguimiento,
            'seguimiento_espera' => $seguimientoEspera,
            'message' => 'Ficha reasignada correctamente a la ventanilla '.$ventanillaDestino->numero.' y puesta como primera en espera.'
        ], 201);
    }

    /**
     * Obtiene la ficha actual (en estado 'llamado' o 'en_atencion') de la ventanilla del usuario autenticado
     * Se expone como público para uso desde otros controladores
     */
    public function getFichaActualVentanilla($usuario)
    {
        if (!$usuario->fk_ventanilla_id) {
            return null;
        }
        
        // Buscar fichas de esta ventanilla que estén realmente en estado activo
        $fichas = \App\Models\Ficha::whereHas('seguimientos', function($q) use ($usuario) {
                $q->where('fk_ventanilla_id', $usuario->fk_ventanilla_id);
            })
            ->with(['seguimientos' => function($q) {
                $q->with('dominioEstado')->latest('created_at');
            }])
            ->get();
            
        // Filtrar por estado actual calculado
        foreach ($fichas as $ficha) {
            $estadoActual = $ficha->estado_actual;
            if (in_array($estadoActual, ['llamado', 'en_atencion'])) {
                return $ficha;
            }
        }
        
        return null;
    }

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
    public function marcarAusente(Request $request, SeguimientoService $seguimientoService)
    {
        $usuario = auth()->user();
        $request->validate([
            'observacion' => 'nullable|string',
        ]);
        
        $ficha = $this->getFichaActualVentanilla($usuario);
        if (!$ficha) {
            return response()->json(['message' => 'No hay ninguna ficha activa para esta ventanilla.'], 404);
        }
        
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'llamado') {
            return response()->json(['message' => 'Solo se puede marcar como ausente una ficha que ha sido llamada y está esperando al usuario.'], 409);
        }
        
        try {
            $dominio = \App\Models\Dominio::where('nombre', 'ausente')->first();
            if (!$dominio) {
                return response()->json(['message' => "No existe un dominio con nombre 'ausente' para el estado de la ficha."], 422);
            }
            $seguimiento = $seguimientoService->crearSeguimiento([
                'fk_ficha_id' => $ficha->ficha_id,
                'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
                'fk_usuario_id' => $usuario->usuario_id,
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
    public function marcarEnAtencion(Request $request, SeguimientoService $seguimientoService)
    {
        $usuario = auth()->user();
        
        $ficha = $this->getFichaActualVentanilla($usuario);
        if (!$ficha) {
            return response()->json(['message' => 'No hay ninguna ficha activa para esta ventanilla.'], 404);
        }
        
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'llamado') {
            return response()->json(['message' => 'Solo se puede poner en atención una ficha que ha sido llamada y está esperando al usuario.'], 409);
        }
        
        $dominioEnAtencion = \App\Models\Dominio::where('nombre', 'en_atencion')->first();
        $seguimiento = $seguimientoService->crearSeguimiento([
            'fk_ficha_id' => $ficha->ficha_id,
            'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
            'fk_usuario_id' => $usuario->usuario_id,
            'estado' => 'en_atencion',
            'fk_dominio_accion_id' => $dominioEnAtencion?->dominio_id,
            'fecha' => now(),
            'observacion' => 'Ficha en atención.'
        ]);
        return response()->json($seguimiento, 201);
    }

    // Marcar ficha como finalizada
    public function marcarFinalizada(Request $request, SeguimientoService $seguimientoService)
    {
        $usuario = auth()->user();
        
        $ficha = $this->getFichaActualVentanilla($usuario);
        if (!$ficha) {
            return response()->json(['message' => 'No hay ninguna ficha activa para esta ventanilla.'], 404);
        }
        
        $estadoActual = $ficha->estado_actual;
        if ($estadoActual !== 'en_atencion') {
            return response()->json(['message' => 'Solo se puede finalizar una ficha en estado "en_atencion".'], 409);
        }
        
        try {
            // Buscar el dominio correspondiente
            $dominio = \App\Models\Dominio::where('nombre', 'finalizado')->first();
            if (!$dominio) {
                return response()->json(['message' => "No existe un dominio con nombre 'finalizado' para el estado de la ficha."], 422);
            }
            $seguimiento = $seguimientoService->crearSeguimiento([
                'fk_ficha_id' => $ficha->ficha_id,
                'fk_ventanilla_id' => $usuario->fk_ventanilla_id,
                'fk_usuario_id' => $usuario->usuario_id,
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
