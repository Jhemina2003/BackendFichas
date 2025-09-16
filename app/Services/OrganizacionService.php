<?php

namespace App\Services;

use App\Models\Organizacion;

class OrganizacionService
{
    public function crearOrganizacion(array $data): Organizacion
    {
        return Organizacion::create($data);
    }

    public function actualizarOrganizacion(Organizacion $organizacion, array $data): Organizacion
    {
        $organizacion->update($data);
        return $organizacion;
    }
}
