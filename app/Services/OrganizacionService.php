<?php

namespace App\Services;

use App\Models\Organizacion;

class OrganizacionService
{
    public function crearOrganizacion(array $data): Organizacion
    {
        // Evitar duplicados
        $existe = \App\Models\Organizacion::where('fk_cod_contacto', $data['fk_cod_contacto'])->first();
        if ($existe) {
            return $existe;
        }

        // Obtener nombre desde RRHH
        $rrhhService = app(\App\Services\Auth\RrhhService::class);
        $info = $rrhhService->getOrganizacion((int)$data['fk_cod_contacto']);
        $nombre = $info['objeto']['nombre'] ?? null;
        if (!$nombre) {
            throw new \Exception('No se pudo obtener el nombre desde RRHH');
        }
        $data['nombre'] = $nombre;
        return \App\Models\Organizacion::create($data);
    }

    public function actualizarOrganizacion(Organizacion $organizacion, array $data): Organizacion
    {
        $organizacion->update($data);
        return $organizacion;
    }
}
