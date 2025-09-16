<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Services\UsuarioService;
use App\Models\Dominio;
use App\Models\DominioGrupo;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::query();
        if ($request->has('sucursal_id')) {
            $query->where('fk_sucursal_id', $request->sucursal_id);
        }
        if ($request->has('tipo_servicio_id')) {
            $query->where('fk_dominio_tipo_servicio_id', $request->tipo_servicio_id);
        }
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }
        if ($request->has('nombre')) {
            $query->where('nombre_completo', 'like', '%'.$request->nombre.'%');
        }
        if ($request->has('correo')) {
            $query->where('correo_electronico', 'like', '%'.$request->correo.'%');
        }
        return $query->get();
    }

    public function show($id)
    {
        return Usuario::findOrFail($id);
    }

    public function store(\App\Http\Requests\StoreUsuarioRequest $request, UsuarioService $usuarioService)
    {
        $usuario = $usuarioService->crearUsuario($request->validated());
        return response()->json($usuario, 201);
    }

    public function update(\App\Http\Requests\UpdateUsuarioRequest $request, $id, UsuarioService $usuarioService)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario = $usuarioService->actualizarUsuario($usuario, $request->validated());
        return response()->json($usuario);
    }

    public function destroy($id)
    {
        Usuario::destroy($id);
        return response()->json(null, 204);
    }
}
