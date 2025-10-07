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
        // Si no se envía fk_sesion_id, buscar o crear la sesión de sucursal activa
        if (empty($fk_sesion_id)) {
            // Buscar sucursal a partir de la ventanilla
            $ventanilla = \App\Models\Ventanilla::findOrFail($fk_ventanilla_id);
            $sucursalId = $ventanilla->fk_sucursal_id;
            $estadoActiva = \App\Enums\EstadoSesionEnum::ACTIVA->value;
            $dominioActiva = \App\Models\Dominio::where('nombre', $estadoActiva)->first();
            $sesion = \App\Models\Sesion::where('fk_sucursal_id', $sucursalId)
                ->whereDate('fecha', Carbon::now()->toDateString())
                ->where('fk_dominio_estado_id', $dominioActiva->dominio_id)
                ->first();
            if (!$sesion) {
                // Crear sesión de sucursal activa
                $sesion = \App\Models\Sesion::create([
                    'fk_sucursal_id' => $sucursalId,
                    'fecha' => Carbon::now(),
                    'fk_dominio_estado_id' => $dominioActiva->dominio_id
                ]);
            }
            $fk_sesion_id = $sesion->sesion_id;
        } else {
            $ventanilla = \App\Models\Ventanilla::findOrFail($fk_ventanilla_id);
        }
        // Verificar si la ventanilla ya está ocupada por OTRO usuario
        $existe = SesionVentanilla::where('fk_sesion_id', $fk_sesion_id)
            ->where('fk_ventanilla_id', $fk_ventanilla_id)
            ->where('fk_usuario_id', '!=', $fk_usuario_id) // Diferente usuario
            ->where('estado', EstadoSesionVentanillaEnum::ACTIVA->value)
            ->first();
        if ($existe) {
            throw new \Exception('Esta ventanilla ya está siendo utilizada por otro usuario.');
        }
        
        // Verificar si este usuario ya tiene una sesión activa en esta ventanilla
        $sesionExistente = SesionVentanilla::where('fk_sesion_id', $fk_sesion_id)
            ->where('fk_usuario_id', $fk_usuario_id)
            ->where('fk_ventanilla_id', $fk_ventanilla_id)
            ->where('estado', EstadoSesionVentanillaEnum::ACTIVA->value)
            ->first();
        if ($sesionExistente) {
            throw new \Exception('Ya tienes una sesión activa en esta ventanilla.');
        }
        // Cambiar estado de la ventanilla a 'abierta'
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
        DB::beginTransaction();
        try {
            $sesion = SesionVentanilla::where('fk_sesion_id', $fk_sesion_id)
                ->where('fk_usuario_id', $fk_usuario_id)
                ->where('fk_ventanilla_id', $fk_ventanilla_id)
                ->where('estado', EstadoSesionVentanillaEnum::ACTIVA->value)
                ->first();
            if (!$sesion) {
                throw new \Exception('No existe una sesión activa para cerrar.');
            }
            
            // Cerrar sesión de ventanilla
            $sesion->estado = EstadoSesionVentanillaEnum::CERRADA->value;
            $sesion->hora_cierre = Carbon::now();
            $sesion->save();
            
            // Cambiar estado de la ventanilla a 'cerrada'
            $ventanilla = \App\Models\Ventanilla::findOrFail($fk_ventanilla_id);
            $ventanilla->estado = \App\Enums\EstadoVentanillaEnum::CERRADA->value;
            $ventanilla->save();
            
            // LÓGICA CRÍTICA: Si todas las ventanillas de la sesión han cerrado, cerrar la sesión general
            if ($this->todasCerradas($fk_sesion_id)) {
                $sesionGeneral = \App\Models\Sesion::findOrFail($fk_sesion_id);
                $estadoCerrada = \App\Models\Dominio::where('nombre', 'cerrada')->first();
                if ($estadoCerrada) {
                    $sesionGeneral->fk_dominio_estado_id = $estadoCerrada->dominio_id;
                    $sesionGeneral->save();
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
