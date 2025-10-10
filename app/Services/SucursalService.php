<?php

namespace App\Services;

use App\Models\Sucursal;

class SucursalService
{
    public function crearSucursal(array $data): Sucursal
    {
        // Evitar duplicados por nombre y organización
        $existe = \App\Models\Sucursal::where('fk_organizacion_id', $data['fk_organizacion_id'])
            ->where('nombre', $data['nombre'])
            ->first();
        if ($existe) {
            return $existe;
        }
        return \App\Models\Sucursal::create($data);
    }

    public function actualizarSucursal(Sucursal $sucursal, array $data): Sucursal
    {
        $sucursal->update($data);
        return $sucursal;
    }
}
