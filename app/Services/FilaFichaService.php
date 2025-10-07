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

        // Obtener los tipos de servicio que puede atender la ventanilla
        $ventanilla = \App\Models\Ventanilla::find($ventanillaId);
        if (!$ventanilla) return null;
        $tiposServicioIds = $ventanilla->tiposServicio()->pluck('dominios.dominio_id')->toArray();
        if (empty($tiposServicioIds)) return null;

        // Obtener todas las fichas y filtrar por estado actual calculado
        $fichasPreferenciales = Ficha::whereHas('tipoFicha', function($q) {
                $q->where('nombre', 'preferencial');
            })
            ->whereHas('tipoServicio', function($q) use ($tiposServicioIds) {
                $q->whereIn('dominio_id', $tiposServicioIds);
            })
            ->orderBy('fecha_registro')
            ->orderBy('ficha_id')
            ->get()
            ->filter(function($ficha) {
                return $ficha->estado_actual === 'en_espera';
            });

        $fichasNormales = Ficha::whereHas('tipoFicha', function($q) {
                $q->where('nombre', 'normal');
            })
            ->whereHas('tipoServicio', function($q) use ($tiposServicioIds) {
                $q->whereIn('dominio_id', $tiposServicioIds);
            })
            ->orderBy('fecha_registro')
            ->orderBy('ficha_id')
            ->get()
            ->filter(function($ficha) {
                return $ficha->estado_actual === 'en_espera';
            });

        $fichaPreferencial = $fichasPreferenciales->first();
        $fichaNormal = $fichasNormales->first();

        // Leer el contador global de atención desde storage
        $counterFile = storage_path('app/turno_counter.txt');
        $counter = ["preferencial" => 0, "normal" => 0];
        if (file_exists($counterFile)) {
            $data = @json_decode(file_get_contents($counterFile), true);
            if (is_array($data)) $counter = $data;
        }

        // Lógica 2 preferenciales, 2 normales
        if (
            ($counter["preferencial"] < 2 && $fichaPreferencial) || (!$fichaNormal && $fichaPreferencial)
        ) {
            $counter["preferencial"]++;
            if ($counter["preferencial"] == 2) {
                $counter["normal"] = 0; // Reset contador normales cuando se completan 2 preferenciales
            }
            file_put_contents($counterFile, json_encode($counter));
            return $fichaPreferencial;
        }
        if ($counter["normal"] < 2 && $fichaNormal) {
            $counter["normal"]++;
            if ($counter["normal"] == 2) {
                $counter["preferencial"] = 0; // Reset contador preferenciales cuando se completan 2 normales
            }
            file_put_contents($counterFile, json_encode($counter));
            return $fichaNormal;
        }
        // Si solo hay preferenciales
        if ($fichaPreferencial) {
            $counter["preferencial"] = 1;
            $counter["normal"] = 0;
            file_put_contents($counterFile, json_encode($counter));
            return $fichaPreferencial;
        }
        // Si solo hay normales
        if ($fichaNormal) {
            $counter["normal"] = 1;
            $counter["preferencial"] = 0;
            file_put_contents($counterFile, json_encode($counter));
            return $fichaNormal;
        }
        return null;
    }
}
