<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Dominio;
use App\Models\DominioGrupo;

class UsuarioController extends Controller
{
    public function index()
    {
        return Usuario::all();
    }

    public function show($id)
    {
        return Usuario::findOrFail($id);
    }

    public function store(Request $request)
    {
        try {
            // Debug información completa
            \Log::info('Headers de la solicitud:', [
                'Content-Type' => $request->header('Content-Type'),
                'Accept' => $request->header('Accept')
            ]);
            \Log::info('Body raw:', ['raw' => $request->getContent()]);
            \Log::info('Body parsed:', $request->all());

            // Validar los datos requeridos
            $validatedData = $request->validate([
                'fk_sucursal_id' => 'required|exists:sucursales,sucursal_id',
                'fk_dominio_tipo_servicio_id' => 'required|exists:dominios,dominio_id',
                'usuario' => 'required|string|max:255',
                'nombre_completo' => 'required|string|max:255',
                'correo_electronico' => 'required|email|max:255',
                'fk_persona_id' => 'required|integer',
                'activo' => 'boolean'
            ]);

            // Verificar que el dominio pertenece al grupo correcto
            $dominio = Dominio::find($request->fk_dominio_tipo_servicio_id);
            if (!$dominio) {
                return response()->json([
                    'error' => 'El dominio no existe',
                    'dominio_id' => $request->fk_dominio_tipo_servicio_id
                ], 422);
            }

            $grupoServicio = DominioGrupo::where('nombre', 'tipo_servicio')->first();
            if (!$grupoServicio) {
                return response()->json([
                    'error' => 'El grupo tipo_servicio no existe en la base de datos',
                    'grupos_disponibles' => DominioGrupo::pluck('nombre')
                ], 422);
            }
            
            if ($dominio->fk_dominio_grupo_id != $grupoServicio->dominio_grupo_id) {
                return response()->json([
                    'error' => 'El dominio tipo servicio no pertenece al grupo correcto',
                    'dominio_id' => $request->fk_dominio_tipo_servicio_id,
                    'grupo_actual' => $dominio->fk_dominio_grupo_id,
                    'grupo_esperado' => $grupoServicio->dominio_grupo_id,
                    'nombre_grupo_esperado' => 'tipo_servicio'
                ], 422);
            }

            $usuario = Usuario::create($validatedData);
            \Log::info('Usuario creado exitosamente:', $usuario->toArray());
            return response()->json($usuario, 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en UsuarioController@store:', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioController@store:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Error al crear el usuario',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update($request->all());
        return response()->json($usuario);
    }

    public function destroy($id)
    {
        Usuario::destroy($id);
        return response()->json(null, 204);
    }
}
