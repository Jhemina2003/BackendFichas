<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncFkPersonaIdCommand extends Command
{
    protected $signature = 'usuarios:sync-fk-persona-id';
    protected $description = 'Sincronizar fk_persona_id de usuarios con el endpoint de RRHH';

    public function handle()
    {
        $this->info('Iniciando sincronización de fk_persona_id...');
        
        // Obtener todos los usuarios de la base de datos local
        $usuarios = Usuario::all();
        $usuariosActualizados = 0;
        
        if ($usuarios->isEmpty()) {
            $this->warn('No se encontraron usuarios en la base de datos.');
            return 0;
        }

        $this->info("Encontrados {$usuarios->count()} usuarios para procesar.");

        // Procesar cada usuario individualmente
        foreach ($usuarios as $usuario) {
            $this->line("Procesando usuario: {$usuario->usuario} ({$usuario->nombre_completo})");

            // Intentar buscar primero por usuario, luego por nombre completo
            $personaEncontrada = $this->buscarPersonaIndividualEnRrhh($usuario->usuario);

            if (!$personaEncontrada && !empty($usuario->nombre_completo)) {
                $this->line("  - Intentando buscar por nombre completo...");
                $personaEncontrada = $this->buscarPersonaIndividualEnRrhh($usuario->nombre_completo);
            }

            // Si no se encuentra, buscar por partes del nombre
            if (!$personaEncontrada && !empty($usuario->nombre_completo)) {
                $partes = preg_split('/\s+/', $usuario->nombre_completo);
                foreach ($partes as $parte) {
                    if (strlen($parte) > 3) { // Evitar palabras muy cortas
                        $this->line("  - Intentando buscar por parte del nombre: $parte");
                        $personaEncontrada = $this->buscarPersonaIndividualEnRrhh($parte);
                        if ($personaEncontrada) {
                            break;
                        }
                    }
                }
            }

            if ($personaEncontrada) {
                $idCorrecto = $personaEncontrada['id'];

                if ($usuario->fk_persona_id != $idCorrecto) {
                    $this->line("  - ID actual: {$usuario->fk_persona_id} -> ID correcto: {$idCorrecto}");

                    $usuario->update(['fk_persona_id' => $idCorrecto]);
                    $usuariosActualizados++;

                    $this->info("  ✓ Usuario actualizado exitosamente");
                } else {
                    $this->line("  - ID ya es correcto: {$idCorrecto}");
                }
            } else {
                $this->warn("  - No se encontró persona en RRHH para usuario: {$usuario->usuario}");
            }
        }

        $this->info("Usuarios actualizados: {$usuariosActualizados}");
        return 0;
    }

    private function obtenerPersonasDeRrhh(): array
    {
        // No intentamos obtener todas las personas de una vez
        // En su lugar, buscaremos cada usuario individualmente
        return [];
    }

    private function buscarPersonaIndividualEnRrhh(string $termino): ?array
    {
        try {
            $this->line("  - Buscando '$termino' en organizaciones RRHH...");
            
            // Usar el endpoint "Usuarios por organizacion" que sí funciona
            // Agregar muchas más organizaciones para buscar (incrementando para encontrar más personas)
            $organizacionesIds = [];
            // Generar IDs de organizaciones en rangos conocidos
            for ($i = 1000; $i <= 1050; $i++) {
                $organizacionesIds[] = $i;
            }
            for ($i = 7000; $i <= 7050; $i++) {
                $organizacionesIds[] = $i;
            }
            
            foreach ($organizacionesIds as $orgId) {
                $this->line("    - Buscando en organización $orgId...");
                
                $response = Http::withHeaders([
                    'RREE-Aplicacion' => 'Plataforma Auditoria',
                    'RREE-ApiKey' => '0f6926a29b7bb551b1b59225db196d5f23085216'
                ])->withOptions(['verify' => false])
                ->get("https://servicios.rree.gob.bo/rrhh-interop/api/v2/personas/uni-organizacional/$orgId");
                
                if ($response->successful()) {
                    $respuesta = $response->json();
                    $personas = $respuesta['lista'] ?? $respuesta['data'] ?? $respuesta ?? [];
                    
                    if (is_array($personas) && count($personas) > 0) {
                        $this->line("    - Encontradas " . count($personas) . " personas en org $orgId");
                        
                        // Buscar por usuario exacto
                        foreach ($personas as $persona) {
                            if (isset($persona['usuario']) && strtolower($persona['usuario']) === strtolower($termino)) {
                                $this->info("  ✓ Encontrado por usuario exacto - ID: {$persona['id']} - Usuario: {$persona['usuario']}");
                                return [
                                    'id' => $persona['id'],
                                    'nombre' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? '',
                                    'nombreCompleto' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? ''
                                ];
                            }
                        }
                        
                        // Debug: mostrar algunos nombres encontrados
                        if (strtolower($termino) === 'jhemina' || strtolower($termino) === 'mayta') {
                            $this->line("    - Nombres encontrados en org $orgId:");
                            foreach (array_slice($personas, 0, 5) as $p) {
                                $this->line("      - " . ($p['nombreCompleto'] ?? $p['nombre'] ?? 'Sin nombre'));
                            }
                        }
                        
                        // Buscar por nombre que contenga el término
                        foreach ($personas as $persona) {
                            $nombre = strtolower($persona['nombreCompleto'] ?? $persona['nombre'] ?? '');
                            $terminoLower = strtolower($termino);
                            
                            if (!empty($nombre) && strpos($nombre, $terminoLower) !== false) {
                                $this->info("  ✓ Encontrado por nombre - ID: {$persona['id']} - Nombre: {$persona['nombreCompleto']}");
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
                                $this->info("  ✓ Encontrado por partes del nombre - ID: {$persona['id']} - Nombre: {$persona['nombreCompleto']}");
                                return [
                                    'id' => $persona['id'],
                                    'nombre' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? '',
                                    'nombreCompleto' => $persona['nombreCompleto'] ?? $persona['nombre'] ?? ''
                                ];
                            }
                        }
                    }
                } else {
                    $this->line("    - Error en org $orgId: " . $response->status());
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->error("  - Error buscando '$termino': " . $e->getMessage());
            return null;
        }
    }


}
