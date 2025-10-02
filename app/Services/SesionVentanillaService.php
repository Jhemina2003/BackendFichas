<?php

namespace App\Services;

use App\Models\SesionVentanilla;
use App\Models\Sesion;
use App\Enums\EstadoSesionVentanillaEnum;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SesionVentanillaService
{
    /**
     * Iniciar sesión de ventanilla
     */
    public function iniciarSesionVentanilla($fk_sesion_id, $fk_usuario_id, $fk_ventanilla_id): SesionVentanilla
    {
        // Verificar si ya existe una sesión activa para este usuario/ventanilla y sesión global
        $existe = SesionVentanilla::where('fk_sesion_id', $fk_sesion_id)
            ->where('fk_usuario_id', $fk_usuario_id)
            ->where('fk_ventanilla_id', $fk_ventanilla_id)
            ->where('estado', EstadoSesionVentanillaEnum::ACTIVA->value)
            ->first();
        if ($existe) {
            throw new \Exception('Ya existe una sesión activa para esta ventanilla.');
        }
        // Cambiar estado de la ventanilla a 'abierta'
        $ventanilla = \App\Models\Ventanilla::findOrFail($fk_ventanilla_id);
        $ventanilla->estado = \App\Enums\EstadoVentanillaEnum::ABIERTA->value;
        $ventanilla->save();
        return SesionVentanilla::create([
            'fk_sesion_id' => $fk_sesion_id,
            'fk_usuario_id' => $fk_usuario_id,
            'fk_ventanilla_id' => $fk_ventanilla_id,
            'estado' => EstadoSesionVentanillaEnum::ACTIVA->value,
            'hora_inicio' => Carbon::now(),
        ]);
    }

    /**
     * Cerrar sesión de ventanilla
     */
    public function cerrarSesionVentanilla($fk_sesion_id, $fk_usuario_id, $fk_ventanilla_id): void
    {
        $sesion = SesionVentanilla::where('fk_sesion_id', $fk_sesion_id)
            ->where('fk_usuario_id', $fk_usuario_id)
            ->where('fk_ventanilla_id', $fk_ventanilla_id)
            ->where('estado', EstadoSesionVentanillaEnum::ACTIVA->value)
            ->first();
        if (!$sesion) {
            throw new \Exception('No existe una sesión activa para cerrar.');
        }
        $sesion->estado = EstadoSesionVentanillaEnum::CERRADA->value;
        $sesion->hora_cierre = Carbon::now();
        $sesion->save();
        // Cambiar estado de la ventanilla a 'cerrada'
        $ventanilla = \App\Models\Ventanilla::findOrFail($fk_ventanilla_id);
        $ventanilla->estado = \App\Enums\EstadoVentanillaEnum::CERRADA->value;
        $ventanilla->save();
    }

    /**
     * Verificar si todas las ventanillas activas han cerrado
     */
    public function todasCerradas($fk_sesion_id): bool
    {
        return !SesionVentanilla::where('fk_sesion_id', $fk_sesion_id)
            ->where('estado', EstadoSesionVentanillaEnum::ACTIVA->value)
            ->exists();
    }
}
