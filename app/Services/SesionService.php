<?php

namespace App\Services;

use App\Models\Sesion;
use App\Models\Dominio;

class SesionService
{
    public function crearSesion(array $data): Sesion
    {
        $data = $this->mapEstadoEnumToDominioId($data);
        return Sesion::create($data);
    }

    public function actualizarSesion(Sesion $sesion, array $data): Sesion
    {
        $data = $this->mapEstadoEnumToDominioId($data);
        $sesion->update($data);
        return $sesion;
    }

    /**
     * Traduce el valor string de Enum a dominio_id para el campo estado
     */
    private function mapEstadoEnumToDominioId(array $data): array
    {
        if (isset($data['estado'])) {
            $dominio = Dominio::where('nombre', ucfirst($data['estado']))->first();
            if ($dominio) {
                $data['fk_dominio_estado_id'] = $dominio->dominio_id;
            }
            unset($data['estado']);
        }
        return $data;
    }
}
