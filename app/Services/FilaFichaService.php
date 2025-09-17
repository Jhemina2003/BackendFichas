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

        // Buscar el dominio_id de tipo_ficha 'prioritaria'
        $dominioPrioritaria = Dominio::where('nombre', 'prioritaria')->first();
        if (!$dominioPrioritaria) return null;

        // 1. Buscar la ficha prioritaria más antigua en espera
        $fichaPrioritaria = Ficha::where('fk_tipo_ficha_id', $dominioPrioritaria->dominio_id)
            ->whereHas('seguimientos', function($q) use ($dominioEspera) {
                $q->where('fk_dominio_estado_id', $dominioEspera->dominio_id);
            })
            ->orderBy('fecha_registro')
            ->orderBy('ficha_id')
            ->first();
        if ($fichaPrioritaria) return $fichaPrioritaria;

        // 2. Si no hay preferenciales, buscar la ficha normal más antigua en espera
        $dominioNormal = Dominio::where('nombre', 'normal')->first();
        if (!$dominioNormal) return null;
        $fichaNormal = Ficha::where('fk_tipo_ficha_id', $dominioNormal->dominio_id)
            ->whereHas('seguimientos', function($q) use ($dominioEspera) {
                $q->where('fk_dominio_estado_id', $dominioEspera->dominio_id);
            })
            ->orderBy('fecha_registro')
            ->orderBy('ficha_id')
            ->first();
        return $fichaNormal;
    }
}
