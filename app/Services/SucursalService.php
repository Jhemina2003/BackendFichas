<?php

namespace App\Services;

use App\Models\Sucursal;

class SucursalService
{
    public function crearSucursal(array $data): Sucursal
    {
        return Sucursal::create($data);
    }

    public function actualizarSucursal(Sucursal $sucursal, array $data): Sucursal
    {
        $sucursal->update($data);
        return $sucursal;
    }
}
