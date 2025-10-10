<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\RolUsuario;

class Token
{
    /**
     * Handle an incoming request.
     * Verifica que el usuario tenga las habilidades/permisos específicos
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Si no se especifican habilidades, permitir acceso
        if (empty($abilities)) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        $userRoles = RolUsuario::where('fk_usuario_id', $user->usuario_id)
            ->join('roles', 'roles.rol_id', '=', 'rol_usuario.fk_rol_id')
            ->pluck('roles.nombre')
            ->toArray();

        foreach ($abilities as $ability) {
            if (in_array($ability, $userRoles)) {
                return $next($request);
            }
        }

        return response()->json([
            'error' => 'No tienes permisos para realizar esta acción',
            'required_roles' => $abilities,
            'user_roles' => $userRoles
        ], 403);
    }
}