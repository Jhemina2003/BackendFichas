<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\RolUsuario;
use Illuminate\Support\Facades\Log;

class Roles
{
    /**
     * Handle an incoming request.
     * Sincroniza roles del usuario desde el token externo y los guarda en la BD local
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Obtener el token RRHH del usuario (guardado durante el login)
        $tokenRrhh = $user->currentAccessToken()?->getAttributes()['token'] ?? null;
        
        if ($tokenRrhh) {
            try {
                // Aquí podrías decodificar el JWT o consultar RRHH para obtener roles
                // Por ahora, verificamos si el usuario ya tiene roles asignados
                $rolesUsuario = RolUsuario::where('fk_usuario_id', $user->usuario_id)->count();
                
                if ($rolesUsuario === 0) {
                    Log::warning('Usuario sin roles asignados', [
                        'usuario_id' => $user->usuario_id,
                        'usuario' => $user->usuario
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error sincronizando roles', [
                    'usuario_id' => $user->usuario_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $next($request);
    }
}