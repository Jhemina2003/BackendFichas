<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RrhhService
{
    private $baseUrl;
    private $apiKey;
    private $aplicacion;

    public function __construct()
    {
    $this->baseUrl = config('services.rrhh.base_url', 'https://servicios.rree.gob.bo');
    $this->apiKey = config('services.rrhh.api_key', '30a9fbece215a821dbb6a0c531547b7f55602b9a');
    $this->aplicacion = config('services.rrhh.aplicacion', 'Anfora Virtual');
    }

    /**
     * Validar credenciales contra el sistema RRHH
     */
    public function validateUser(array $credentials): ?array
    {
        try {
            Log::info('Intentando autenticación RRHH', [
                'usuario' => $credentials['usuario'] ?? 'N/A'
            ]);

            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->post($this->baseUrl . '/autenticacion/api/token', [
                    'a' => $credentials['usuario'],
                    'b' => $credentials['password'],
                    'c' => 'Personal',
                    'd' => request()->ip()
                ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                // Si el login es exitoso, obtener datos completos del usuario
                if (isset($tokenData['token']) || isset($tokenData['access_token'])) {
                    $userData = $this->getUserDetails($credentials['usuario'], $tokenData);
                    Log::info('Autenticación RRHH exitosa', [
                        'usuario' => $credentials['usuario']
                    ]);
                    // Adjuntar el token externo al array de usuario
                    if (isset($tokenData['token'])) {
                        $userData['token_rrhh'] = $tokenData['token'];
                    } elseif (isset($tokenData['access_token'])) {
                        $userData['token_rrhh'] = $tokenData['access_token'];
                    }
                    return $userData;
                }
            }

            Log::warning('Credenciales inválidas en RRHH', [
                'usuario' => $credentials['usuario'],
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error en autenticación RRHH', [
                'usuario' => $credentials['usuario'] ?? 'N/A',
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Obtener detalles del usuario desde RRHH
     */
    public function getUserDetails(string $usuario, array $tokenData): array
    {
        try {
            // Buscar usuario por nombre
            $searchResponse = Http::withHeaders([
                'RREE-ApiKey' => $this->apiKey,
                'RREE-Aplicacion' => $this->aplicacion
            ])->withOptions(['verify' => false])
            ->post($this->baseUrl . '/rrhh-interop/api/V2/Personas/buscar', [
                'nombre' => $usuario
            ]);

            if ($searchResponse->successful()) {
                $searchData = $searchResponse->json();
                
                if (isset($searchData['data']) && count($searchData['data']) > 0) {
                    $persona = $searchData['data'][0]; // Tomar el primer resultado
                    
                    // Obtener detalles completos si tenemos el ID
                    if (isset($persona['id'])) {
                        $detailResponse = Http::withHeaders([
                            'Rree-Apikey' => $this->apiKey,
                            'Rree-Aplicacion' => $this->aplicacion
                        ])->withOptions(['verify' => false])
                        ->get($this->baseUrl . '/rrhh-interop/api/v2/personas/autenticacion/' . $persona['id']);
                        
                        if ($detailResponse->successful()) {
                            $detailData = $detailResponse->json();
                            
                            return [
                                'id' => $persona['id'],
                                'usuario' => $usuario,
                                'nombre_completo' => $detailData['nombreCompleto'] ?? $persona['nombreCompleto'] ?? $usuario,
                                'correo_electronico' => $detailData['correoElectronico'] ?? $persona['correoElectronico'] ?? $usuario . '@rree.gob.bo',
                                'fk_persona_id' => $persona['id'],
                                'organizacion_id' => $detailData['unidadOrganizacional']['id'] ?? null,
                                'organizacion_nombre' => $detailData['unidadOrganizacional']['nombre'] ?? null,
                                'cargo' => $detailData['cargo'] ?? null,
                                'activo' => $detailData['estado'] ?? true
                            ];
                        }
                    }
                    
                    // Si no se pueden obtener detalles, usar datos básicos
                    return [
                        'id' => $persona['id'] ?? null,
                        'usuario' => $usuario,
                        'nombre_completo' => $persona['nombreCompleto'] ?? $usuario,
                        'correo_electronico' => $persona['correoElectronico'] ?? $usuario . '@rree.gob.bo',
                        'fk_persona_id' => $persona['id'] ?? null,
                        'organizacion_id' => null,
                        'organizacion_nombre' => null,
                        'cargo' => null,
                        'activo' => true
                    ];
                }
            }
            
            // Si no se encuentra información detallada, crear datos básicos
            return [
                'id' => null,
                'usuario' => $usuario,
                'nombre_completo' => $usuario,
                'correo_electronico' => $usuario . '@rree.gob.bo',
                'fk_persona_id' => null,
                'organizacion_id' => null,
                'organizacion_nombre' => null,
                'cargo' => null,
                'activo' => true
            ];
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo detalles de usuario RRHH', [
                'usuario' => $usuario,
                'error' => $e->getMessage()
            ]);
            
            // Retornar datos mínimos en caso de error
            return [
                'id' => null,
                'usuario' => $usuario,
                'nombre_completo' => $usuario,
                'correo_electronico' => $usuario . '@rree.gob.bo',
                'fk_persona_id' => null,
                'organizacion_id' => null,
                'organizacion_nombre' => null,
                'cargo' => null,
                'activo' => true
            ];
        }
    }

    /**
     * Obtener información de organización
     */
    public function getOrganizacion(int $organizacionId): ?array
    {
        try {
            $response = Http::withHeaders([
                'RREE-Aplicacion' => $this->aplicacion,
                'RREE-ApiKey' => $this->apiKey
            ])->withOptions(['verify' => false])
            ->get('https://www.rree.gob.bo/backend/organizacion-interop/api/V2/unidades-organizacionales/' . $organizacionId);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Error obteniendo organización', [
                'organizacion_id' => $organizacionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Listar organizaciones desde RRHH
     */
    public function listarOrganizaciones(): ?array
    {
        try {
            $response = Http::withHeaders([
                'RREE-Aplicacion' => $this->aplicacion,
                'RREE-ApiKey' => $this->apiKey
            ])->withOptions(['verify' => false])
            ->get('https://www.rree.gob.bo/backend/organizacion-interop/api/V2/unidades-organizacionales/codificador?u=SC');

            Log::info('RRHH organizaciones response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Error listando organizaciones RRHH', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Listar usuarios por organización desde RRHH
     */
    public function getUsuariosPorOrganizacion($organizacionId): ?array
    {
        try {
            $response = Http::withHeaders([
                'RREE-Aplicacion' => 'Plataforma Auditoria',
                'RREE-ApiKey' => '0f6926a29b7bb551b1b59225db196d5f23085216'
            ])->withOptions(['verify' => false])
            ->get('https://servicios.rree.gob.bo/rrhh-interop/api/v2/personas/uni-organizacional/' . $organizacionId);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Error listando usuarios por organización RRHH', [
                'organizacion_id' => $organizacionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Buscar usuarios por nombre en RRHH
     */
    public function buscarUsuariosPorNombre($nombre): ?array
    {
        try {
            $response = Http::withHeaders([
                'RREE-ApiKey' => '5d2a42fdad5a4159ad4cea5491f95471fddfbf30',
                'RREE-Aplicacion' => 'Soporte'
            ])->withOptions(['verify' => false])
            ->post('https://servicios.rree.gob.bo/rrhh-interop/api/V2/Personas/buscar', [
                'nombre' => $nombre
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Error buscando usuarios por nombre RRHH', [
                'nombre' => $nombre,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtener detalle de usuario desde RRHH
     */
    public function getUsuarioDetalle($usuarioId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Rree-Apikey' => $this->apiKey,
                'Rree-Aplicacion' => $this->aplicacion
            ])->withOptions(['verify' => false])
            ->get('https://www.rree.gob.bo/backend/rrhh-interop/api/v2/personas/autenticacion/' . $usuarioId);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Error obteniendo detalle de usuario RRHH', [
                'usuario_id' => $usuarioId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}