<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Llamada;
use App\Services\LlamadaService;
use App\Services\FilaFichaService;
use Illuminate\Support\Facades\Log;

class LlamadaController extends Controller
{
    protected $filaFichaService;

    public function __construct(FilaFichaService $filaFichaService)
    {
        $this->filaFichaService = $filaFichaService;
    }
    /**
     * Llama a la siguiente ficha en espera (preferencial primero), crea la llamada y retorna la ficha llamada.
     * POST /api/llamadas/llamar-siguiente
     * El usuario autenticado debe tener fk_ventanilla_id asignado.
     */
    public function llamarSiguiente(Request $request, LlamadaService $llamadaService)
    {
        try {
            $usuario = auth()->user();
            if (!$usuario || !$usuario->fk_ventanilla_id) {
                return response()->json(['message' => 'Usuario no autenticado o sin ventanilla asignada'], 403);
            }
            
            $ventanillaId = $usuario->fk_ventanilla_id;
            
            \Log::info('Intento de llamar ficha', [
                'usuario' => $usuario->usuario,
                'usuario_id' => $usuario->usuario_id,
                'ventanilla_id' => $ventanillaId
            ]);
            
            // Validar que la ventanilla tenga una sesión activa
            $sesionActiva = \App\Models\SesionVentanilla::where('fk_ventanilla_id', $ventanillaId)
                ->where('fk_usuario_id', $usuario->usuario_id)
                ->where('estado', 'activa')
                ->whereNull('hora_cierre')
                ->first();
                
            \Log::info('Validación de sesión ventanilla', [
                'usuario_id' => $usuario->usuario_id,
                'ventanilla_id' => $ventanillaId,
                'sesion_encontrada' => $sesionActiva ? 'SÍ' : 'NO',
                'sesion_data' => $sesionActiva ? $sesionActiva->toArray() : null
            ]);
                
            if (!$sesionActiva) {
                return response()->json(['message' => 'Debe iniciar sesión en la ventanilla antes de llamar fichas'], 403);
            }
            
            // Validar que no tenga una ficha activa (llamado o en_atencion)
            $fichaActiva = app(\App\Http\Controllers\Api\SeguimientoController::class)
                ->getFichaActualVentanilla($usuario);
            if ($fichaActiva) {
                return response()->json(['message' => 'No puede llamar una nueva ficha mientras tenga una ficha activa. Debe finalizar la ficha actual primero.'], 409);
            }
            
            $ficha = $this->filaFichaService->siguienteFichaEnEsperaPorVentanilla($ventanillaId);
            if (!$ficha) {
                return response()->json(['message' => 'No hay fichas en espera para los servicios activos de esta ventanilla'], 404);
            }
            
            // Usar transacción para evitar condiciones de carrera
            try {
                \DB::beginTransaction();
                
                // Re-verificar estado dentro de la transacción
                $ficha->refresh();
                $estadoActual = $ficha->estado_actual;
                if ($estadoActual !== 'en_espera') {
                    \DB::rollBack();
                    return response()->json(['message' => 'La ficha ya fue llamada o atendida por otro operador.'], 409);
                }
                
                // Crear la llamada
                $llamada = $llamadaService->crearLlamada([
                    'fk_usuario_id' => $usuario->usuario_id,
                    'fk_ventanilla_id' => $ventanillaId,
                    'fk_ficha_id' => $ficha->ficha_id,
                    'fecha' => now(),
                ]);
                
                // Cambiar el estado del seguimiento de la ficha a "llamado"
                $dominioLlamado = \App\Models\Dominio::where('nombre', 'llamado')->first();
                if (!$dominioLlamado) {
                    \DB::rollBack();
                    return response()->json(['message' => 'Error del sistema: dominio "llamado" no encontrado'], 500);
                }
                
                \App\Models\Seguimiento::create([
                    'fk_ficha_id' => $ficha->ficha_id,
                    'fk_ventanilla_id' => $ventanillaId,
                    'fk_usuario_id' => $usuario->usuario_id,
                    'fk_dominio_estado_id' => $dominioLlamado->dominio_id,
                    'fk_dominio_accion_id' => $dominioLlamado->dominio_id,
                    'fecha' => now(),
                    'observacion' => 'Ficha llamada a ventanilla.'
                ]);
                
                \DB::commit();
                
                return response()->json([
                    'llamada' => $llamada,
                    'ficha' => $ficha
                ], 201);
                
            } catch (\Exception $e) {
                \DB::rollBack();
                return response()->json(['message' => 'Error al procesar la llamada: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function index(Request $request)
    {
        $query = Llamada::query();
        if ($request->has('ficha_id')) {
            $query->where('fk_ficha_id', $request->ficha_id);
        }
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('ventanilla_id')) {
            $query->where('fk_ventanilla_id', $request->ventanilla_id);
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }
        return $query->get();
    }

    public function show($id)
    {
        return Llamada::findOrFail($id);
    }

    public function store(\App\Http\Requests\StoreLlamadaRequest $request, LlamadaService $llamadaService)
    {
        try {
            $llamada = $llamadaService->crearLlamada($request->validated());
            return response()->json($llamada, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(\App\Http\Requests\UpdateLlamadaRequest $request, $id, LlamadaService $llamadaService)
    {
        try {
            $llamada = Llamada::findOrFail($id);
            $llamada = $llamadaService->actualizarLlamada($llamada, $request->validated());
            return response()->json($llamada);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Llamada no encontrada'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        Llamada::destroy($id);
        return response()->json(null, 204);
    }
}
