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
        // LÓGICA DE CIERRE FORZADO: Al iniciar ventanilla, verificar y cerrar fichas del día anterior
        $this->forzarCierreFichasAnteriores($fk_ventanilla_id);

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

    /**
     * Forzar cierre de fichas y sesiones del día anterior al iniciar ventanilla
     */
    private function forzarCierreFichasAnteriores($fk_ventanilla_id): void
    {
        $ventanilla = \App\Models\Ventanilla::with('sucursal')->findOrFail($fk_ventanilla_id);
        $sucursalId = $ventanilla->sucursal->sucursal_id;
        $hoy = Carbon::now()->toDateString();
        $ayer = Carbon::now()->subDay()->toDateString();
        
        $dominioActiva = \App\Models\Dominio::where('nombre', 'activa')->first();
        $dominioCerrada = \App\Models\Dominio::where('nombre', 'cerrada')->first();
        $dominioVentanillaActiva = EstadoSesionVentanillaEnum::ACTIVA->value;
        $dominioVentanillaCerrada = EstadoSesionVentanillaEnum::CERRADA->value;
        
        // Buscar sesiones anteriores a hoy que sigan activas
        $sesionesAnteriores = \App\Models\Sesion::where('fk_sucursal_id', $sucursalId)
            ->whereDate('fecha', '<', $hoy)
            ->where('fk_dominio_estado_id', $dominioActiva ? $dominioActiva->dominio_id : null)
            ->get();
        
        $observaciones = [];
        
        foreach ($sesionesAnteriores as $sesionAnterior) {
            // Buscar ventanillas abiertas en esa sesión
            $ventanillasAnteriores = SesionVentanilla::where('fk_sesion_id', $sesionAnterior->sesion_id)
                ->where('estado', $dominioVentanillaActiva)
                ->get();
                
            foreach ($ventanillasAnteriores as $sv) {
                // Forzar cierre de sesión de ventanilla
                $sv->estado = $dominioVentanillaCerrada;
                $sv->hora_cierre = Carbon::now();
                $sv->save();
                
                // Buscar fichas inconclusas (en_espera, llamado, en_atencion)
                $fichasInconclusas = \App\Models\Ficha::where('fk_sesion_id', $sesionAnterior->sesion_id)
                    ->whereHas('seguimientos', function($q) {
                        $q->whereHas('dominioEstado', function($sub) {
                            $sub->whereIn('nombre', ['en_espera', 'llamado', 'en_atencion']);
                        })->latest('fecha');
                    })
                    ->get()
                    ->filter(function($ficha) {
                        return in_array($ficha->estado_actual, ['en_espera', 'llamado', 'en_atencion']);
                    });
                
                if ($fichasInconclusas->count() > 0) {
                    $ventanillaInfo = $sv->ventanilla;
                    $observaciones[] = 'Ventanilla #' . ($ventanillaInfo ? $ventanillaInfo->numero : $sv->fk_ventanilla_id) . 
                        ' dejó ' . $fichasInconclusas->count() . ' fichas inconclusas el ' . $sesionAnterior->fecha;
                    
                    // Finalizar automáticamente las fichas inconclusas
                    $dominioFinalizado = \App\Models\Dominio::where('nombre', 'finalizado')->first();
                    foreach ($fichasInconclusas as $fichaInconclusa) {
                        $estadoActual = $fichaInconclusa->estado_actual;
                        $mensajeObservacion = '';
                        
                        switch ($estadoActual) {
                            case 'en_espera':
                                $mensajeObservacion = 'Ficha finalizada automáticamente - quedó en espera sin ser llamada el día anterior.';
                                break;
                            case 'llamado':
                                $mensajeObservacion = 'Ficha finalizada automáticamente - fue llamada pero el usuario no se presentó el día anterior.';
                                break;
                            case 'en_atencion':
                                $mensajeObservacion = 'Ficha finalizada automáticamente - estaba en atención pero no se completó el día anterior.';
                                break;
                        }
                        
                        // Crear seguimiento de finalización forzada
                        \App\Models\Seguimiento::create([
                            'fk_ficha_id' => $fichaInconclusa->ficha_id,
                            'fk_ventanilla_id' => $sv->fk_ventanilla_id,
                            'fk_usuario_id' => null, // Sin usuario específico (cierre automático)
                            'fk_dominio_estado_id' => $dominioFinalizado ? $dominioFinalizado->dominio_id : null,
                            'fk_dominio_accion_id' => $dominioFinalizado ? $dominioFinalizado->dominio_id : null,
                            'fecha' => Carbon::now(),
                            'observacion' => $mensajeObservacion
                        ]);
                    }
                }
            }
            
            // Forzar cierre de sesión general
            $sesionAnterior->fk_dominio_estado_id = $dominioCerrada ? $dominioCerrada->dominio_id : $sesionAnterior->fk_dominio_estado_id;
            $sesionAnterior->save();
        }
        
        // Si hay observaciones, registrar en la sesión actual del día
        if (count($observaciones) > 0) {
            $sesionHoy = \App\Models\Sesion::where('fk_sucursal_id', $sucursalId)
                ->whereDate('fecha', $hoy)
                ->where('fk_dominio_estado_id', $dominioActiva ? $dominioActiva->dominio_id : null)
                ->first();
                
            if ($sesionHoy && empty($sesionHoy->observacion)) {
                $sesionHoy->observacion = 'Cierre forzado: ' . implode(' | ', $observaciones);
                $sesionHoy->save();
            }
        }
    }
}
