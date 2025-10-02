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
     * pero solo de los servicios activos de la ventanilla indicada.
     * @param int $ventanillaId
     */
    public function siguienteFichaEnEsperaPorVentanilla(int $ventanillaId): ?Ficha
    {
        $dominioEspera = Dominio::where('nombre', 'en_espera')->first();
        if (!$dominioEspera) return null;

        // Obtener los servicios activos de la ventanilla
        $serviciosIds = \App\Models\ServicioVentanilla::where('fk_ventanilla_id', $ventanillaId)
            ->where('activo', true)
            ->pluck('fk_servicio_id')
            ->toArray();
        if (empty($serviciosIds)) return null;

        // Obtener la siguiente ficha preferencial y normal en espera, solo de los servicios activos
        $fichaPreferencial = Ficha::whereHas('tipoFicha', function($q) {
                $q->where('nombre', 'preferencial');
            })
            ->whereIn('fk_servicio_id', $serviciosIds)
            ->whereHas('seguimientos', function($q) use ($dominioEspera) {
                $q->where('fk_dominio_estado_id', $dominioEspera->dominio_id);
            })
            ->orderBy('fecha_registro')
            ->orderBy('ficha_id')
            ->first();

        $fichaNormal = Ficha::whereHas('tipoFicha', function($q) {
                $q->where('nombre', 'normal');
            })
            ->whereIn('fk_servicio_id', $serviciosIds)
            ->whereHas('seguimientos', function($q) use ($dominioEspera) {
                $q->where('fk_dominio_estado_id', $dominioEspera->dominio_id);
            })
            ->orderBy('fecha_registro')
            ->orderBy('ficha_id')
            ->first();

        // Leer el contador global de atención desde storage
        $counterFile = storage_path('app/turno_counter.txt');
        $counter = ["preferencial" => 0, "normal" => 0];
        if (file_exists($counterFile)) {
            $data = @json_decode(file_get_contents($counterFile), true);
            if (is_array($data)) $counter = $data;
        }

        // Lógica 2 preferenciales, 2 normales
        if (
            ($counter["preferencial"] < 2 && $fichaPreferencial) || !$fichaNormal
        ) {
            $counter["preferencial"]++;
            if ($counter["preferencial"] == 2) $counter["normal"] = 0;
            file_put_contents($counterFile, json_encode($counter));
            return $fichaPreferencial;
        }
        if ($counter["normal"] < 2 && $fichaNormal) {
            $counter["normal"]++;
            if ($counter["normal"] == 2) $counter["preferencial"] = 0;
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
