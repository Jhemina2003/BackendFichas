<?php

namespace App\Services;

use App\Models\Llamada;

class LlamadaService
{
    public function crearLlamada(array $data): Llamada
    {
        return Llamada::create($data);
    }

    public function actualizarLlamada(Llamada $llamada, array $data): Llamada
    {
        $llamada->update($data);
        return $llamada;
    }
}
