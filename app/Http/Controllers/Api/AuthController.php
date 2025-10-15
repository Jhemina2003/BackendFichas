<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Auth\SignInService;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private $signInService;

    public function __construct(SignInService $signInService)
    {
        $this->signInService = $signInService;
    }

    /**
     * Autenticar usuario usando RRHH
     */
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $authData = $this->signInService->authenticate($request->only(['usuario', 'password']));

            // Respuesta explícita con ambos tokens
            return response()->json([
                'token' => $authData['token'],
                'token_rrhh' => $authData['token_rrhh'],
                'token_type' => $authData['token_type'],
                'user' => $authData['user'],
                'message' => 'Autenticación exitosa'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en login', [
                'usuario' => $request->usuario,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error de autenticación',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Cerrar sesión del usuario
     */
    public function logout(Request $request)
    {
        try {
            $usuario = $request->user();
            $success = $this->signInService->logout($usuario);

            if ($success) {
                return response()->json([
                    'message' => 'Sesión cerrada exitosamente'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Error al cerrar sesión'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error en logout', [
                'user_id' => $request->user()?->usuario_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        try {
            Log::info('Endpoint /me llamado', [
                'user_id' => $request->user()?->usuario_id,
                'token_exists' => $request->bearerToken() ? 'Sí' : 'No'
            ]);

            $usuario = $request->user();
            if (!$usuario) {
                Log::warning('Token inválido o usuario no autenticado en /me');
                return response()->json([
                    'message' => 'No autorizado'
                ], 401);
            }

            $usuario->load(['sucursal', 'ventanilla', 'roles']);

            Log::info('Usuario obtenido exitosamente en /me', [
                'usuario_id' => $usuario->usuario_id,
                'usuario' => $usuario->usuario,
                'roles' => $usuario->roles->pluck('nombre')
            ]);

            return response()->json([
                'message' => 'Usuario obtenido exitosamente',
                'data' => [
                    'user' => $usuario
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener información del usuario', [
                'user_id' => $request->user()?->usuario_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener información del usuario'
            ], 500);
        }
    }

    /**
     * Renovar token de autenticación
     */
    public function refresh(Request $request)
    {
        try {
            $usuario = $request->user();
            $newToken = $this->signInService->refreshToken($usuario);

            return response()->json([
                'message' => 'Token renovado exitosamente',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al renovar token', [
                'user_id' => $request->user()?->usuario_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al renovar token'
            ], 500);
        }
    }
}
