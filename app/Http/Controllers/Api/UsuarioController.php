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
    $query = Usuario::with(['ventanilla', 'sucursal']);
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
    // Actualizar fk_persona_id desde RRHH
    $rrhhService = app(\App\Services\Auth\RrhhService::class);
    $usuarioService->actualizarFkPersonaIdDesdeRrhh($usuario, $rrhhService);
    return response()->json($usuario, 201);
    }

    public function update(\App\Http\Requests\UpdateUsuarioRequest $request, $id, UsuarioService $usuarioService)
    {
    $usuario = Usuario::findOrFail($id);
    $usuario = $usuarioService->actualizarUsuario($usuario, $request->validated());
    // Actualizar fk_persona_id desde RRHH
    $rrhhService = app(\App\Services\Auth\RrhhService::class);
    $usuarioService->actualizarFkPersonaIdDesdeRrhh($usuario, $rrhhService);
    return response()->json($usuario);
    }

    public function destroy($id)
    {
        Usuario::destroy($id);
        return response()->json(null, 204);
    }

    /**
     * Asignar rol a un usuario
     */
    public function asignarRol(Request $request, $usuario_id)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,rol_id'
        ]);

        $usuario = Usuario::findOrFail($usuario_id);
        // Verificar si ya tiene el rol
        if ($usuario->roles()->where('rol_id', $request->rol_id)->exists()) {
            return response()->json([
                'message' => 'El usuario ya tiene este rol asignado'
            ], 409);
        }
        $usuario->roles()->attach($request->rol_id);
        // Actualizar fk_persona_id desde RRHH
        $rrhhService = app(\App\Services\Auth\RrhhService::class);
        $usuarioService = app(UsuarioService::class);
        $usuarioService->actualizarFkPersonaIdDesdeRrhh($usuario, $rrhhService);
        return response()->json([
            'message' => 'Rol asignado correctamente',
            'usuario' => $usuario->load('roles')
        ]);
    }

    /**
     * Asignar rol a un usuario vía API y actualizar fk_persona_id
     */
    public function asignarRolApi(Request $request, $usuario_id)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,rol_id'
        ]);

        $usuario = Usuario::findOrFail($usuario_id);
        // Verificar si ya tiene el rol
        if ($usuario->roles()->where('rol_id', $request->rol_id)->exists()) {
            return response()->json([
                'message' => 'El usuario ya tiene este rol asignado'
            ], 409);
        }
        $usuario->roles()->attach($request->rol_id);
        // Actualizar fk_persona_id desde RRHH
        $rrhhService = app(\App\Services\Auth\RrhhService::class);
        $usuarioService = app(UsuarioService::class);
        $usuarioService->actualizarFkPersonaIdDesdeRrhh($usuario, $rrhhService);
        return response()->json([
            'message' => 'Rol asignado correctamente y fk_persona_id actualizado',
            'usuario' => $usuario->load('roles')
        ]);
    }

    /**
     * Quitar rol de un usuario
     */
    public function quitarRol(Request $request, $usuario_id)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,rol_id'
        ]);

        $usuario = Usuario::findOrFail($usuario_id);
        $usuario->roles()->detach($request->rol_id);

        return response()->json([
            'message' => 'Rol removido correctamente',
            'usuario' => $usuario->load('roles')
        ]);
    }

    /**
     * Listar usuarios con sus roles (para administración)
     */
    public function listarConRoles()
    {
        $usuarios = Usuario::with(['roles', 'sucursal', 'ventanilla'])
            ->orderBy('nombre_completo')
            ->get();

        return response()->json($usuarios);
    }

    /**
     * Asignar ventanilla a un usuario vía API y actualizar fk_persona_id
     */
    public function asignarVentanillaApi(Request $request, $usuario_id)
    {
        $request->validate([
            'ventanilla_id' => 'required|exists:ventanillas,ventanilla_id'
        ]);

        $usuario = Usuario::findOrFail($usuario_id);
        // Verificar si el usuario tiene rol administrador o superadministrador
        $roles = $usuario->roles->pluck('nombre')->map(function($r) { return strtolower($r); })->toArray();
        if (in_array('administrador', $roles) || in_array('superadministrador', $roles)) {
            return response()->json([
                'message' => 'No se puede asignar ventanilla ni sucursal a un usuario con rol administrador o superadministrador.',
                'usuario' => $usuario->load(['roles', 'ventanilla', 'sucursal'])
            ], 403);
        }

        $ventanilla = \App\Models\Ventanilla::findOrFail($request->ventanilla_id);
        $sucursalId = $ventanilla->fk_sucursal_id;
        $usuario->update([
            'fk_ventanilla_id' => $ventanilla->ventanilla_id,
            'fk_sucursal_id' => $sucursalId
        ]);

        // Actualizar fk_persona_id desde RRHH
        $rrhhService = app(\App\Services\Auth\RrhhService::class);
        $usuarioService = app(UsuarioService::class);
        $usuarioService->actualizarFkPersonaIdDesdeRrhh($usuario, $rrhhService);

        // Recargar el usuario desde la base de datos para asegurar datos actualizados
        $usuarioActualizado = \App\Models\Usuario::with(['roles', 'ventanilla', 'sucursal'])->find($usuario->usuario_id);
        return response()->json([
            'message' => 'Ventanilla y sucursal asignadas correctamente, fk_persona_id actualizado',
            'usuario' => $usuarioActualizado
        ]);
    }
}
