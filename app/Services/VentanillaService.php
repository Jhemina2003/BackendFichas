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
        
        // Obtener la organización de la ventanilla
        $organizacion = Organizacion::find($sucursal->fk_organizacion_id);
        if (!$organizacion) {
            throw new \Exception('Organización no encontrada para la ventanilla.');
        }

        // Obtener el servicio RRHH y validar usando "usuarios por organización"
        $rrhhService = app(\App\Services\Auth\RrhhService::class);
        $usuariosOrganizacion = $rrhhService->getUsuariosPorOrganizacion($organizacion->fk_cod_contacto);
        
        if (!$usuariosOrganizacion) {
            throw new \Exception('No se pudo obtener la lista de usuarios de la organización desde RRHH.');
        }

        // Verificar si el usuario está en la lista de usuarios de la organización
        $usuarioEncontrado = false;
        $listaUsuarios = $usuariosOrganizacion['lista'] ?? $usuariosOrganizacion;
        
        foreach ($listaUsuarios as $usuarioRRHH) {
            $idUsuario = $usuarioRRHH['id'] ?? null;
                        
            if ($idUsuario && $idUsuario == $usuario->fk_persona_id) {
                $usuarioEncontrado = true;
                break;
            }
        }

        if (!$usuarioEncontrado) {
            // Log para debugging
            \Illuminate\Support\Facades\Log::info('Usuario no encontrado en organización RRHH', [
                'usuario_fk_persona_id' => $usuario->fk_persona_id,
                'organizacion_id' => $organizacion->fk_cod_contacto,
                'usuarios_rrhh_structure' => array_slice($usuariosOrganizacion, 0, 2) // Solo los primeros 2 para ver estructura
            ]);
            throw new \Exception('El usuario no pertenece a la organización de la ventanilla según RRHH.');
        }

        // Asignar usuario a ventanilla
        $usuario->fk_ventanilla_id = $ventanilla->ventanilla_id;
        $usuario->save();
    }
}
