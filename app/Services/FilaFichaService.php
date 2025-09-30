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
    public function siguienteFichaEnEspera(): ?Ficha
    {
        // Buscar el dominio_id de 'en_espera'
        $dominioEspera = Dominio::where('nombre', 'en_espera')->first();
        if (!$dominioEspera) return null;

        // Buscar el dominio_id de prioridad 'preferencial'
        $dominioPreferencial = Dominio::where('nombre', 'preferencial')->first();
        if ($dominioPreferencial) {
            $fichaPreferencial = Ficha::where('fk_prioridad_ficha_id', $dominioPreferencial->dominio_id)
                ->whereHas('seguimientos', function($q) use ($dominioEspera) {
                    $q->where('fk_dominio_estado_id', $dominioEspera->dominio_id);
                })
                ->orderBy('fecha_registro')
                ->orderBy('ficha_id')
                ->first();
            if ($fichaPreferencial) return $fichaPreferencial;
        }
        // Si no hay preferenciales, buscar la ficha normal más antigua en espera
        $dominioNormal = Dominio::where('nombre', 'normal')->first();
        if ($dominioNormal) {
            $fichaNormal = Ficha::where('fk_prioridad_ficha_id', $dominioNormal->dominio_id)
                ->whereHas('seguimientos', function($q) use ($dominioEspera) {
                    $q->where('fk_dominio_estado_id', $dominioEspera->dominio_id);
                })
                ->orderBy('fecha_registro')
                ->orderBy('ficha_id')
                ->first();
            return $fichaNormal;
        }
        return null;
    }
}
