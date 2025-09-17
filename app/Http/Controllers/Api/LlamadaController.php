<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Llamada;
use App\Services\LlamadaService;
use App\Services\FilaFichaService;

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
     * Body: { "fk_usuario_id": int, "fk_ventanilla_id": int }
     */
    public function llamarSiguiente(Request $request, LlamadaService $llamadaService)
    {
        $request->validate([
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
        ]);
        $ficha = $this->filaFichaService->siguienteFichaEnEspera();
        if (!$ficha) {
            return response()->json(['message' => 'No hay fichas en espera'], 404);
        }
        // Crear la llamada
        $llamada = $llamadaService->crearLlamada([
            'fk_usuario_id' => $request->fk_usuario_id,
            'fk_ventanilla_id' => $request->fk_ventanilla_id,
            'fk_ficha_id' => $ficha->ficha_id,
            'fecha' => now(),
        ]);
        // (Opcional) Cambiar el estado del seguimiento de la ficha a "llamado"
        // ...
        return response()->json([
            'llamada' => $llamada,
            'ficha' => $ficha
        ], 201);
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
        $llamada = $llamadaService->crearLlamada($request->validated());
        return response()->json($llamada, 201);
    }

    public function update(\App\Http\Requests\UpdateLlamadaRequest $request, $id, LlamadaService $llamadaService)
    {
        $llamada = Llamada::findOrFail($id);
        $llamada = $llamadaService->actualizarLlamada($llamada, $request->validated());
        return response()->json($llamada);
    }

    public function destroy($id)
    {
        Llamada::destroy($id);
        return response()->json(null, 204);
    }
}
