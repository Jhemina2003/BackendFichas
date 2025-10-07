<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Dominio;
use Illuminate\Support\Carbon;

class FilaFichaService
{
    /**
     * Devuelve la siguiente ficha a llamar en la fila general, priorizando las preferenciales.
     * Solo fichas en estado 'en_espera'.
     */
    /**
     * Devuelve la siguiente ficha a llamar en la fila general, priorizando las preferenciales,
     * para todos los tipos de servicio (apostilla, legalizaciones, vivencia, devoluciones).
     * @param int $ventanillaId
     */
    public function siguienteFichaEnEsperaPorVentanilla(int $ventanillaId): ?Ficha
    {
        $dominioEspera = Dominio::where('nombre', 'en_espera')->first();
        if (!$dominioEspera) return null;

        // Obtener la ventanilla y su sucursal para filtrar correctamente
        $ventanilla = \App\Models\Ventanilla::with('sucursal')->find($ventanillaId);
        if (!$ventanilla || !$ventanilla->sucursal) return null;
        
        $sucursalId = $ventanilla->sucursal->sucursal_id;
        $tiposServicioIds = $ventanilla->tiposServicio()->pluck('dominios.dominio_id')->toArray();
        if (empty($tiposServicioIds)) return null;

        // Obtener fichas SOLO de la misma sucursal, filtradas por estado actual calculado
        // Ordenadas por fecha del último seguimiento "en_espera" para respetar reasignaciones
        $fichasPreferenciales = Ficha::whereHas('tipoFicha', function($q) {
                $q->where('nombre', 'preferencial');
            })
            ->whereHas('tipoServicio', function($q) use ($tiposServicioIds) {
                $q->whereIn('dominio_id', $tiposServicioIds);
            })
            ->whereHas('sesion.sucursal', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            })
            ->with(['seguimientos' => function($q) {
                $q->whereHas('dominioEstado', function($subQ) {
                    $subQ->where('nombre', 'en_espera');
                })->latest('fecha');
            }])
            ->get()
            ->filter(function($ficha) {
                return $ficha->estado_actual === 'en_espera';
            })
            ->sortBy(function($ficha) {
                $ultimoEnEspera = $ficha->seguimientos->first();
                return $ultimoEnEspera ? $ultimoEnEspera->fecha : $ficha->fecha_registro;
            });

        $fichasNormales = Ficha::whereHas('tipoFicha', function($q) {
                $q->where('nombre', 'normal');
            })
            ->whereHas('tipoServicio', function($q) use ($tiposServicioIds) {
                $q->whereIn('dominio_id', $tiposServicioIds);
            })
            ->whereHas('sesion.sucursal', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            })
            ->with(['seguimientos' => function($q) {
                $q->whereHas('dominioEstado', function($subQ) {
                    $subQ->where('nombre', 'en_espera');
                })->latest('fecha');
            }])
            ->get()
            ->filter(function($ficha) {
                return $ficha->estado_actual === 'en_espera';
            })
            ->sortBy(function($ficha) {
                $ultimoEnEspera = $ficha->seguimientos->first();
                return $ultimoEnEspera ? $ultimoEnEspera->fecha : $ficha->fecha_registro;
            });

        $fichaPreferencial = $fichasPreferenciales->first();
        $fichaNormal = $fichasNormales->first();

        // Leer el contador global de atención con lock para evitar condiciones de carrera
        $counterFile = storage_path('app/turno_counter.txt');
        $lockFile = storage_path('app/turno_counter.lock');
        
        $counter = ["preferencial" => 0, "normal" => 0];
        
        // Intentar obtener lock por máximo 5 segundos
        $lockHandle = fopen($lockFile, 'w');
        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            // Si no puede obtener lock inmediatamente, esperar un poco
            usleep(100000); // 100ms
            if (!flock($lockHandle, LOCK_EX)) {
                fclose($lockHandle);
                throw new \Exception('No se pudo obtener lock para el contador de turnos');
            }
        }
        
        try {
            if (file_exists($counterFile)) {
                $content = file_get_contents($counterFile);
                if ($content !== false) {
                    $data = json_decode($content, true);
                    if (is_array($data) && isset($data['preferencial']) && isset($data['normal'])) {
                        $counter = $data;
                    }
                }
            }
            
            // Lógica 2 preferenciales, 2 normales (sin cambios)
            $fichaSeleccionada = null;
            
            if (
                ($counter["preferencial"] < 2 && $fichaPreferencial) || (!$fichaNormal && $fichaPreferencial)
            ) {
                $counter["preferencial"]++;
                if ($counter["preferencial"] == 2) {
                    $counter["normal"] = 0;
                }
                $fichaSeleccionada = $fichaPreferencial;
            } elseif ($counter["normal"] < 2 && $fichaNormal) {
                $counter["normal"]++;
                if ($counter["normal"] == 2) {
                    $counter["preferencial"] = 0;
                }
                $fichaSeleccionada = $fichaNormal;
            } elseif ($fichaPreferencial) {
                $counter["preferencial"] = 1;
                $counter["normal"] = 0;
                $fichaSeleccionada = $fichaPreferencial;
            } elseif ($fichaNormal) {
                $counter["normal"] = 1;
                $counter["preferencial"] = 0;
                $fichaSeleccionada = $fichaNormal;
            }
            
            // Escribir contador actualizado
            if ($fichaSeleccionada) {
                file_put_contents($counterFile, json_encode($counter));
            }
            
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
        
        return $fichaSeleccionada;
    }
}
