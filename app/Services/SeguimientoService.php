<?php

namespace App\Services;

use App\Models\Seguimiento;
use App\Models\Dominio;

class SeguimientoService
{
    public function crearSeguimiento(array $data): Seguimiento
    {
        $data = $this->mapEstadoEnumToDominioId($data);
        return Seguimiento::create($data);
    }

    public function actualizarSeguimiento(Seguimiento $seguimiento, array $data): Seguimiento
    {
        $data = $this->mapEstadoEnumToDominioId($data);
        $seguimiento->update($data);
        return $seguimiento;
    }

    /**
     * Traduce el valor string de Enum a dominio_id para el campo estado
     */
    private function mapEstadoEnumToDominioId(array $data): array
    {
        if (isset($data['estado'])) {
            $nombre = $data['estado'];
            $dominio = Dominio::where('nombre', $nombre)->first();
            if (!$dominio) {
                throw new \Exception("No existe un dominio con nombre '$nombre' para el estado de la ficha.");
            }
            $data['fk_dominio_estado_id'] = $dominio->dominio_id;
            unset($data['estado']);
        }
        return $data;
    }
}
