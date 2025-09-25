<?php

namespace App\Services;

use App\Models\Llamada;

class LlamadaService
{
    public function crearLlamada(array $data): Llamada
    {
        $llamada = Llamada::create($data);
        // Si la llamada está asociada a una ficha, incrementar cantidad_llamadas y actualizar fk_llamada_id
        if (isset($data['fk_ficha_id'])) {
            $ficha = \App\Models\Ficha::find($data['fk_ficha_id']);
            if ($ficha) {
                $ficha->increment('cantidad_llamadas');
                $ficha->fk_llamada_id = $llamada->llamada_id;
                $ficha->save();
            }
        }
        return $llamada;
    }

    public function actualizarLlamada(Llamada $llamada, array $data): Llamada
    {
        $llamada->update($data);
        return $llamada;
    }
}
