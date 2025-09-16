<?php

namespace App\Services;

use App\Models\Ventanilla;

class VentanillaService
{
    public function crearVentanilla(array $data): Ventanilla
    {
        return Ventanilla::create($data);
    }

    public function actualizarVentanilla(Ventanilla $ventanilla, array $data): Ventanilla
    {
        $ventanilla->update($data);
        return $ventanilla;
    }
}
