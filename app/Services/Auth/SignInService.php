<?php

namespace App\Services\Auth;

use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\Organizacion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SignInService
{
    private $rrhhService;

    public function __construct(RrhhService $rrhhService)
    {
        $this->rrhhService = $rrhhService;
    }

    /**
     * Autenticar usuario contra RRHH y crear/actualizar usuario local
     */
    public function authenticate(array $credentials): array
    {
        // Validar credenciales requeridas
        if (empty($credentials['usuario']) || empty($credentials['password'])) {
            throw new \Exception('Usuario y contraseña son requeridos');
        }

        // Validar contra RRHH
        $personaData = $this->rrhhService->validateUser($credentials);
        // El token externo debe venir del RRHH
        $tokenExterno = null;
        if (is_array($personaData) && isset($personaData['token_rrhh'])) {
            $tokenExterno = $personaData['token_rrhh'];
        } elseif (is_array($personaData) && isset($personaData['token'])) {
            $tokenExterno = $personaData['token'];
        }

        if (!$personaData) {
            throw new \Exception('Credenciales inválidas');
        }

        try {
            DB::beginTransaction();

            // No crear organización automáticamente al autenticar
            // La organización se agregará manualmente desde el panel de administración

            // Buscar o crear usuario local SIN sucursal y SIN organización automática
            $usuario = $this->findOrCreateLocalUserSinSucursal($personaData, $credentials['password']);

            // Generar token Sanctum
            $token = $usuario->createToken('auth-token', ['*'], now()->addHours(24))->plainTextToken;

            DB::commit();

            Log::info('Usuario autenticado exitosamente', [
                'usuario_id' => $usuario->usuario_id,
                'usuario' => $usuario->usuario
            ]);

            return [
                'user' => $usuario->load(['sucursal', 'ventanilla']),
                'token' => $token,
                'token_type' => 'Bearer',
                'token_rrhh' => $tokenExterno
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en proceso de autenticación', [
                'usuario' => $credentials['usuario'],
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Error interno del servidor durante la autenticación');
        }
    }

    /**
     * Buscar o crear usuario en la base de datos local
     */
    private function findOrCreateLocalUser(array $personaData, string $password): Usuario
    {
        throw new \Exception('Este método no debe usarse más. Utiliza findOrCreateLocalUserSinSucursal.');

    }

    /**
     * Buscar o crear usuario en la base de datos local SIN sucursal
     */
    private function findOrCreateLocalUserSinSucursal(array $personaData, string $password): Usuario
    {
        $usuario = Usuario::where('usuario', $personaData['usuario'])->first();

        if ($usuario) {
            $usuario->update([
                'nombre_completo' => $personaData['nombre_completo'],
                'correo_electronico' => $personaData['correo_electronico'],
                'password' => Hash::make($password),
                'fk_persona_id' => $personaData['fk_persona_id'] ?? $usuario->fk_persona_id,
                'fk_sucursal_id' => null, // No asignar sucursal
                'fk_ventanilla_id' => null,
                'activo' => $personaData['activo'] ?? true
            ]);
            Log::info('Usuario existente actualizado', [
                'usuario_id' => $usuario->usuario_id,
                'usuario' => $usuario->usuario
            ]);
        } else {
            $usuario = Usuario::create([
                'usuario' => $personaData['usuario'],
                'nombre_completo' => $personaData['nombre_completo'],
                'correo_electronico' => $personaData['correo_electronico'],
                'password' => Hash::make($password),
                'fk_persona_id' => $personaData['fk_persona_id'] ?? rand(1000, 9999),
                'fk_sucursal_id' => null, // No asignar sucursal
                'fk_ventanilla_id' => null,
                'activo' => $personaData['activo'] ?? true
            ]);
            Log::info('Nuevo usuario creado', [
                'usuario_id' => $usuario->usuario_id,
                'usuario' => $usuario->usuario
            ]);
        }
        return $usuario;
    }

    /**
     * Determinar sucursal para el usuario basado en su organización
     */
    private function determinarSucursal(array $personaData): int
    {
        // Si ya tiene sucursal asignada localmente, mantenerla
        $usuarioExistente = Usuario::where('usuario', $personaData['usuario'])->first();
        if ($usuarioExistente && $usuarioExistente->fk_sucursal_id) {
            return $usuarioExistente->fk_sucursal_id;
        }

        // Buscar organización local que coincida
        if (!empty($personaData['organizacion_nombre'])) {
            $organizacion = Organizacion::where('nombre', 'LIKE', '%' . $personaData['organizacion_nombre'] . '%')
                ->orWhere('nombre', 'LIKE', '%Apostilla%')
                ->orWhere('nombre', 'LIKE', '%Legalizaciones%')
                ->first();

            if ($organizacion) {
                $sucursal = Sucursal::where('fk_organizacion_id', $organizacion->organizacion_id)->first();
                if ($sucursal) {
                    return $sucursal->sucursal_id;
                }
            }
        }

        // Asignar a la primera sucursal disponible como fallback
        $sucursalPorDefecto = Sucursal::first();
        
        if (!$sucursalPorDefecto) {
            // Si no hay sucursales, crear una por defecto
            $organizacionPorDefecto = Organizacion::firstOrCreate(
                ['nombre' => 'Unidad Apostilla y Legalizaciones'],
                ['sigla' => 'UAL']
            );

            $sucursalPorDefecto = Sucursal::create([
                'fk_organizacion_id' => $organizacionPorDefecto->organizacion_id,
                'nombre' => 'Santa Cruz'
            ]);
        }

        Log::warning('Usuario asignado a sucursal por defecto', [
            'usuario' => $personaData['usuario'],
            'sucursal_id' => $sucursalPorDefecto->sucursal_id,
            'razon' => 'No se pudo determinar sucursal específica'
        ]);

        return $sucursalPorDefecto->sucursal_id;
    }

    /**
     * Cerrar sesión (invalidar token)
     */
    public function logout(Usuario $usuario): bool
    {
        try {
            // Eliminar todos los tokens del usuario
            $usuario->tokens()->delete();

            Log::info('Usuario deslogueado', [
                'usuario_id' => $usuario->usuario_id,
                'usuario' => $usuario->usuario
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al cerrar sesión', [
                'usuario_id' => $usuario->usuario_id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Renovar token de usuario
     */
    public function refreshToken(Usuario $usuario): string
    {
        // Eliminar tokens existentes
        $usuario->tokens()->delete();

        // Crear nuevo token
        $token = $usuario->createToken('auth-token', ['*'], now()->addHours(24))->plainTextToken;

        Log::info('Token renovado', [
            'usuario_id' => $usuario->usuario_id,
            'usuario' => $usuario->usuario
        ]);

        return $token;
    }
}