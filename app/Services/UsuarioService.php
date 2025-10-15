<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UsuarioService
{
    /**
     * Actualiza el fk_persona_id del usuario usando RRHH con búsqueda exhaustiva
     */
    public function actualizarFkPersonaIdDesdeRrhh(Usuario $usuario, \App\Services\Auth\RrhhService $rrhhService): void
    {
        Log::info("Actualizando fk_persona_id para usuario: {$usuario->usuario} ({$usuario->nombre_completo})");

        // Solo buscar y asignar si la coincidencia es exacta por usuario
        $personaEncontrada = null;
        $orgIds = [];
        for ($i = 1000; $i <= 1050; $i++) $orgIds[] = $i;
        for ($i = 7000; $i <= 7050; $i++) $orgIds[] = $i;
        foreach ($orgIds as $orgId) {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'RREE-Aplicacion' => 'Plataforma Auditoria',
                'RREE-ApiKey' => '0f6926a29b7bb551b1b59225db196d5f23085216'
            ])->withOptions(['verify' => false])
            ->get("https://servicios.rree.gob.bo/rrhh-interop/api/v2/personas/uni-organizacional/$orgId");
            if ($response->successful()) {
                $respuesta = $response->json();
                $personas = $respuesta['lista'] ?? $respuesta['data'] ?? $respuesta ?? [];
                if (is_array($personas) && count($personas) > 0) {
                    foreach ($personas as $persona) {
                        if (isset($persona['usuario']) && strtolower($persona['usuario']) === strtolower($usuario->usuario)) {
                            $personaEncontrada = $persona;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($personaEncontrada) {
            $idCorrecto = $personaEncontrada['id'];
            if (empty($usuario->fk_persona_id)) {
                Log::info("ID actual vacío, asignando: {$idCorrecto}");
                $usuario->update(['fk_persona_id' => $idCorrecto]);
                Log::info("✓ Usuario actualizado exitosamente");
            } elseif ($usuario->fk_persona_id == $idCorrecto) {
                Log::info("ID ya es correcto y no se modifica: {$idCorrecto}");
            } else {
                Log::info("ID ya está asignado manualmente ({$usuario->fk_persona_id}), no se sobrescribe por RRHH");
            }
        } else {
            Log::warning("No se encontró coincidencia exacta en RRHH para usuario: {$usuario->usuario}");
        }
    }

    /**
     * Buscar persona individual en RRHH usando la misma lógica del comando de sincronización
     */
    private function buscarPersonaIndividualEnRrhh(string $termino): ?array
    {
        try {
            Log::info("Buscando '$termino' en organizaciones RRHH...");
            
            // Usar el endpoint "Usuarios por organizacion" que sí funciona
            $organizacionesIds = [];
            // Generar IDs de organizaciones en rangos conocidos
            for ($i = 1000; $i <= 1050; $i++) {
                $organizacionesIds[] = $i;
            }
            for ($i = 7000; $i <= 7050; $i++) {
                $organizacionesIds[] = $i;
            }
            
            foreach ($organizacionesIds as $orgId) {
                $response = Http::withHeaders([
                    'RREE-Aplicacion' => 'Plataforma Auditoria',
                    'RREE-ApiKey' => '0f6926a29b7bb551b1b59225db196d5f23085216'
                ])->withOptions(['verify' => false])
                ->get("https://servicios.rree.gob.bo/rrhh-interop/api/v2/personas/uni-organizacional/$orgId");
                
                if ($response->successful()) {
                    $respuesta = $response->json();
                    $personas = $respuesta['lista'] ?? $respuesta['data'] ?? $respuesta ?? [];
                    
                    if (is_array($personas) && count($personas) > 0) {
                        // Buscar por usuario exacto
                        foreach ($personas as $persona) {
                            if (isset($persona['usuario']) && strtolower($persona['usuario']) === strtolower($termino)) {
                                Log::info("✓ Encontrado por usuario exacto - ID: {$persona['id']} - Usuario: {$persona['usuario']}");
                                return [
                                    'id' => $persona['id'],
                                    'nombre' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? '',
                                    'nombreCompleto' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? ''
                                ];
                            }
                        }
                        
                        // Buscar por nombre que contenga el término
                        foreach ($personas as $persona) {
                            $nombre = strtolower($persona['nombreCompleto'] ?? $persona['nombre'] ?? '');
                            $terminoLower = strtolower($termino);
                            
                            if (!empty($nombre) && strpos($nombre, $terminoLower) !== false) {
                                Log::info("✓ Encontrado por nombre - ID: {$persona['id']} - Nombre: {$persona['nombreCompleto']}");
                                return [
                                    'id' => $persona['id'],
                                    'nombre' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? '',
                                    'nombreCompleto' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? ''
                                ];
                            }
                        }
                        
                        // Buscar por partes del nombre
                        $partes = preg_split('/\s+/', $termino);
                        foreach ($personas as $persona) {
                            $nombre = strtolower($persona['nombreCompleto'] ?? $persona['nombre'] ?? '');
                            $coincidencias = 0;
                            
                            foreach ($partes as $parte) {
                                if (strlen($parte) > 2 && strpos($nombre, strtolower($parte)) !== false) {
                                    $coincidencias++;
                                }
                            }
                            
                            if ($coincidencias >= ceil(count($partes) * 0.6)) {
                                Log::info("✓ Encontrado por partes del nombre - ID: {$persona['id']} - Nombre: {$persona['nombreCompleto']}");
                                return [
                                    'id' => $persona['id'],
                                    'nombre' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? '',
                                    'nombreCompleto' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? ''
                                ];
                            }
                        }
                    }
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error buscando persona en RRHH: " . $e->getMessage());
            return null;
        }
    }
    public function crearUsuario(array $data): Usuario
    {
        return Usuario::create($data);
    }

    public function actualizarUsuario(Usuario $usuario, array $data): Usuario
    {
        $usuario->update($data);
        return $usuario;
    }
}
