<?php

namespace App\Services;

use App\Models\Ventanilla;


use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\Organizacion;

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

    /**
     * Asigna un usuario a una ventanilla, validando unicidad y organización.
     */
    public function asignarUsuario(Ventanilla $ventanilla, Usuario $usuario): void
    {
        // Validar que la ventanilla no tenga ya un usuario asignado
        $usuarioActual = Usuario::where('fk_ventanilla_id', $ventanilla->ventanilla_id)->first();
        if ($usuarioActual && $usuarioActual->usuario_id !== $usuario->usuario_id) {
            throw new \Exception('La ventanilla ya tiene un usuario asignado.');
        }

        // Validar que el usuario pertenezca a la misma organización usando RRHH
        $sucursal = Sucursal::find($ventanilla->fk_sucursal_id);
        if (!$sucursal) {
            throw new \Exception('Sucursal no encontrada para la ventanilla.');
        }
        $orgVentanilla = $sucursal->fk_organizacion_id;

        // Obtener el servicio RRHH
        $rrhhService = app(\App\Services\Auth\RrhhService::class);
        $usuarioDetalle = $rrhhService->getUsuarioDetalle($usuario->fk_persona_id);
        $orgUsuario = $usuarioDetalle['unidadOrganizacional']['id'] ?? null;
        if ($orgUsuario != $orgVentanilla) {
            throw new \Exception('El usuario no pertenece a la organización de la ventanilla según RRHH.');
        }

        // Asignar usuario a ventanilla
        $usuario->fk_ventanilla_id = $ventanilla->ventanilla_id;
        $usuario->save();
    }
}
