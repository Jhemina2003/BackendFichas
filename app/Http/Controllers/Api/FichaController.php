<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Services\FichaService;



class FichaController extends Controller
{

    /**
     * Devuelve la cantidad de fichas en espera, finalizadas y ausentes.
     */
    public function estadisticas()
    {
        try {
            $estados = ['en_espera', 'finalizado', 'ausente'];
            $result = [];
            $hoy = now()->toDateString();
            $ventanillaId = request('ventanilla_id');
            
            foreach ($estados as $estado) {
                // Buscar el dominio del estado
                $dominio = \App\Models\Dominio::where('nombre', $estado)->first();
                if (!$dominio) {
                    $result[$estado] = 0;
                    continue;
                }
                
                // Contar seguimientos del día actual con este estado
                $query = \App\Models\Seguimiento::where('fk_dominio_estado_id', $dominio->dominio_id)
                    ->whereDate('fecha', $hoy);
                    
                if ($ventanillaId) {
                    $query->where('fk_ventanilla_id', $ventanillaId);
                }
                
                $result[$estado] = $query->count();
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
// ...existing code...
    public function index(Request $request)
    {
    $query = Ficha::with(['usuario', 'sesion', 'tipoFicha', 'tipoServicio']);
        if ($request->has('sesion_id')) {
            $query->where('fk_sesion_id', $request->sesion_id);
        }
        if ($request->has('tipo_ficha_id')) {
            $query->where('fk_tipo_ficha_id', $request->tipo_ficha_id);
        }
        if ($request->has('tipo_servicio_id')) {
            $query->where('fk_tipo_servicio_id', $request->tipo_servicio_id);
        }
        if ($request->has('prioridad_ficha_id')) {
            $query->where('fk_prioridad_ficha_id', $request->prioridad_ficha_id);
        }
        if ($request->has('fecha')) {
            $query->whereDate('fecha_registro', $request->fecha);
        }
        if ($request->has('estado')) {
            $estadoNombre = $request->estado;
            $dominio = \App\Models\Dominio::where('nombre', $estadoNombre)->first();
            if ($dominio) {
                $query->where('fk_dominio_tipo_id', $dominio->dominio_id);
            } else {
                return response()->json([]);
            }
        }
        if ($request->has('usuario_id')) {
            $query->where('fk_usuario_id', $request->usuario_id);
        }
        if ($request->has('sucursal_id')) {
            $query->whereHas('sesion', function($q) use ($request) {
                $q->where('fk_sucursal_id', $request->sucursal_id);
            });
        }
    return $query->get();
    }

    public function store(\App\Http\Requests\StoreFichaRequest $request, FichaService $fichaService)
    {
        try {
            $ficha = $fichaService->crearFicha($request->validated());
            $ficha = $ficha->fresh(['sesion.sucursal.organizacion']);

            $organizacionNombre = $ficha->sesion && $ficha->sesion->sucursal && $ficha->sesion->sucursal->organizacion
                ? $ficha->sesion->sucursal->organizacion->nombre
                : null;
            $sucursalNombre = $ficha->sesion && $ficha->sesion->sucursal
                ? $ficha->sesion->sucursal->nombre
                : null;

            return response()->json([
                'numero_formateado' => $ficha->numero_formateado,
                'fecha_registro' => $ficha->fecha_registro,
                'organizacion' => $organizacionNombre,
                'sucursal' => $sucursalNombre,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
    return Ficha::findOrFail($id);
    }


    public function update(\App\Http\Requests\UpdateFichaRequest $request, $id, FichaService $fichaService)
    {
        try {
            $ficha = Ficha::findOrFail($id);
            $ficha = $fichaService->actualizarFicha($ficha, $request->validated());
            return response()->json($ficha->fresh());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Ficha no encontrada'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        Ficha::destroy($id);
        return response()->json(null, 204);
    }
}
