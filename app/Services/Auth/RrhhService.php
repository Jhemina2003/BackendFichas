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
                Log::info('Respuesta RRHH autenticación', [
                    'usuario' => $credentials['usuario'],
                    'tokenData' => $tokenData
                ]);
                
                // Si el login es exitoso, obtener datos completos del usuario
                if (isset($tokenData['token']) || isset($tokenData['access_token'])) {
                    $userData = $this->getUserDetails($credentials['usuario'], $tokenData);
                    Log::info('Autenticación RRHH exitosa', [
                        'usuario' => $credentials['usuario'],
                        'userData' => $userData
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
     * Obtener detalles del usuario desde RRHH usando el endpoint correcto
     */
    public function getUserDetails(string $usuario, array $tokenData): array
    {
        try {
            // Decodificar JWT para obtener nombre completo si existe
            $jwtPayload = null;
            $nombreCompleto = $usuario;
            $organizacionId = null;
            $organizacionNombre = null;
            if (isset($tokenData['token'])) {
                $jwtPayload = $this->decodeJWT($tokenData['token']);
                if ($jwtPayload) {
                    $nombreCompleto = $jwtPayload['NombreCompleto'] ?? $usuario;
                    $organizacionId = $jwtPayload['UniOrganizacionalID'] ?? null;
                    $organizacionNombre = $jwtPayload['UniOrganizacional'] ?? null;
                }
            }

            // 1. Buscar coincidencia exacta por usuario
            $searchResponse = Http::withHeaders([
                'RREE-ApiKey' => '5d2a42fdad5a4159ad4cea5491f95471fddfbf30',
                'RREE-Aplicacion' => 'Soporte'
            ])->withOptions(['verify' => false])
            ->post('https://servicios.rree.gob.bo/rrhh-interop/api/V2/Personas/buscar', [
                'nombre' => $usuario
            ]);

            $personaId = null;
            $personaData = null;

            if ($searchResponse->successful()) {
                $searchData = $searchResponse->json();
                if (isset($searchData['data']) && count($searchData['data']) > 0) {
                    foreach ($searchData['data'] as $persona) {
                        if (isset($persona['usuario']) && strtolower($persona['usuario']) === strtolower($usuario)) {
                            $personaId = $persona['id'];
                            $personaData = $persona;
                            Log::info('Persona encontrada en RRHH por coincidencia exacta de usuario', [
                                'usuario' => $usuario,
                                'persona_id' => $personaId,
                                'nombre' => $personaData['nombreCompleto'] ?? ''
                            ]);
                            break;
                        }
                    }
                }
            }

            // 2. Si no hay coincidencia exacta, buscar por nombre completo en todas las organizaciones
            if (!$personaId) {
                Log::info('No se encontró coincidencia exacta, buscando por nombre en todas las organizaciones', [
                    'usuario' => $usuario,
                    'nombreCompleto' => $nombreCompleto
                ]);
                $organizaciones = $this->listarOrganizaciones();
                if ($organizaciones && isset($organizaciones['data'])) {
                    foreach ($organizaciones['data'] as $org) {
                        $orgId = $org['id'] ?? null;
                        if ($orgId) {
                            $usuariosOrg = $this->getUsuariosPorOrganizacion($orgId);
                            if ($usuariosOrg && isset($usuariosOrg['data'])) {
                                foreach ($usuariosOrg['data'] as $persona) {
                                    // Comparar nombre completo ignorando tildes, mayúsculas y espacios extras
                                    $nombrePersona = strtolower(trim(preg_replace('/\s+/', ' ', iconv('UTF-8', 'ASCII//TRANSLIT', $persona['nombreCompleto'] ?? ''))));
                                    $nombreBuscado = strtolower(trim(preg_replace('/\s+/', ' ', iconv('UTF-8', 'ASCII//TRANSLIT', $nombreCompleto))));
                                    Log::debug('Comparando nombres RRHH', [
                                        'usuario' => $usuario,
                                        'nombrePersona' => $nombrePersona,
                                        'nombreBuscado' => $nombreBuscado,
                                        'persona_id' => $persona['id'] ?? null,
                                        'organizacion_id' => $orgId
                                    ]);
                                    if ($nombrePersona === $nombreBuscado || strpos($nombrePersona, $nombreBuscado) !== false || strpos($nombreBuscado, $nombrePersona) !== false) {
                                        $personaId = $persona['id'] ?? null;
                                        $personaData = $persona;
                                        Log::info('Persona encontrada en RRHH por nombre en organización', [
                                            'usuario' => $usuario,
                                            'persona_id' => $personaId,
                                            'organizacion_id' => $orgId,
                                            'nombre' => $personaData['nombreCompleto'] ?? ''
                                        ]);
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Si se encontró el nombre pero no el id, buscar en el endpoint local /api/rrhh/personas
            if (!$personaId && !empty($nombreCompleto)) {
                try {
                    $localResponse = Http::timeout(5)->get('http://127.0.0.1:8000/api/rrhh/personas');
                    if ($localResponse->successful()) {
                        $personasLocal = $localResponse->json();
                        foreach ($personasLocal as $personaLocal) {
                            // El endpoint local usa 'nombre' en lugar de 'nombreCompleto'
                            $nombreLocal = strtolower(trim(preg_replace('/\s+/', ' ', iconv('UTF-8', 'ASCII//TRANSLIT', $personaLocal['nombre'] ?? ''))));
                            $nombreBuscado = strtolower(trim(preg_replace('/\s+/', ' ', iconv('UTF-8', 'ASCII//TRANSLIT', $nombreCompleto))));
                            Log::debug('Comparando nombres endpoint local', [
                                'usuario' => $usuario,
                                'nombreLocal' => $nombreLocal,
                                'nombreBuscado' => $nombreBuscado,
                                'persona_id' => $personaLocal['id']
                            ]);
                            if ($nombreLocal === $nombreBuscado || strpos($nombreLocal, $nombreBuscado) !== false || strpos($nombreBuscado, $nombreLocal) !== false) {
                                $personaId = $personaLocal['id'];
                                $personaData = ['nombreCompleto' => $personaLocal['nombre']]; // Mapear correctamente
                                Log::info('Persona encontrada en endpoint local /api/rrhh/personas', [
                                    'usuario' => $usuario,
                                    'persona_id' => $personaId,
                                    'nombre' => $personaLocal['nombre']
                                ]);
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error consultando endpoint local /api/rrhh/personas', [
                        'usuario' => $usuario,
                        'error' => $e->getMessage()
                    ]);
                }
            }            // 3. Si sigue sin encontrarse, buscar por correo electrónico
            if (!$personaId && isset($personaData['correoElectronico'])) {
                $correoBuscado = strtolower($usuario . '@rree.gob.bo');
                if (strtolower($personaData['correoElectronico']) === $correoBuscado) {
                    $personaId = $personaData['id'];
                    Log::info('Persona encontrada en RRHH por correo electrónico', [
                        'usuario' => $usuario,
                        'persona_id' => $personaId,
                        'correo' => $personaData['correoElectronico']
                    ]);
                }
            }

            // 4. Retornar datos completos si se encontró persona
            if ($personaId) {
                return [
                    'id' => $personaId,
                    'usuario' => $usuario,
                    'nombre_completo' => $personaData['nombreCompleto'] ?? $nombreCompleto,
                    'correo_electronico' => $personaData['correoElectronico'] ?? $usuario . '@rree.gob.bo',
                    'fk_persona_id' => $personaId,
                    'organizacion_id' => $organizacionId,
                    'organizacion_nombre' => $organizacionNombre,
                    'cargo' => $personaData['cargo'] ?? null,
                    'activo' => true
                ];
            } else {
                Log::warning('No se encontró coincidencia en RRHH tras búsqueda avanzada, se crea sin fk_persona_id', [
                    'usuario' => $usuario,
                    'nombreCompleto' => $nombreCompleto
                ]);
                return [
                    'id' => null,
                    'usuario' => $usuario,
                    'nombre_completo' => $nombreCompleto,
                    'correo_electronico' => $usuario . '@rree.gob.bo',
                    'fk_persona_id' => null,
                    'organizacion_id' => $organizacionId,
                    'organizacion_nombre' => $organizacionNombre,
                    'cargo' => null,
                    'activo' => true
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo detalles de usuario RRHH', [
                'usuario' => $usuario,
                'error' => $e->getMessage()
            ]);
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
     * Decodificar JWT sin verificar la firma (solo extraer payload)
     */
    private function decodeJWT(string $jwt): ?array
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return null;
            }
            
            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            return json_decode($payload, true);
        } catch (\Exception $e) {
            Log::error('Error decodificando JWT', ['error' => $e->getMessage()]);
            return null;
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