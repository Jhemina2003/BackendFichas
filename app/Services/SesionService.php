<?php

namespace App\Services;

use App\Models\Sesion;
use App\Models\Dominio;

class SesionService {

    /**
     * Reabrir una sesión cerrada, validando que no exista otra activa para la sucursal y fecha
     */
    public function reabrirSesion(Sesion $sesion): Sesion
    {
        $estadoActiva = \App\Enums\EstadoSesionEnum::ACTIVA->value;
        $dominioActiva = \App\Models\Dominio::where('nombre', $estadoActiva)->first();
        if (!$dominioActiva) {
            throw new \Exception('Error interno: No se encontró el dominio para el estado "activa". Contacte a soporte.');
        }
        // Solo se puede reabrir si está cerrada
        $estadoCerrada = \App\Enums\EstadoSesionEnum::CERRADA->value;
        $dominioCerrada = \App\Models\Dominio::where('nombre', $estadoCerrada)->first();
        if (!$dominioCerrada) {
            throw new \Exception('Error interno: No se encontró el dominio para el estado "cerrada". Contacte a soporte.');
        }
        if ($sesion->fk_dominio_estado_id != $dominioCerrada->dominio_id) {
            throw new \Exception('Solo se puede reabrir una sesión que esté en estado "cerrada".');
        }
        // Validar que no exista otra sesión activa para la sucursal y fecha
        $existe = Sesion::where('fk_sucursal_id', $sesion->fk_sucursal_id)
            ->whereDate('fecha', date('Y-m-d', strtotime($sesion->fecha)))
            ->where('fk_dominio_estado_id', $dominioActiva->dominio_id)
            ->exists();
        if ($existe) {
            throw new \Exception('No se puede reabrir la sesión: ya existe una sesión activa para esta sucursal y fecha.');
        }
        $sesion->fk_dominio_estado_id = $dominioActiva->dominio_id;
        $sesion->save();
        return $sesion;
    }
    public function crearSesion(array $data): Sesion
    {
        // Si no se envía estado, asumir "activa"
        if (empty($data['estado']) && empty($data['fk_dominio_estado_id'])) {
            $data['estado'] = \App\Enums\EstadoSesionEnum::ACTIVA->value;
        }
        $data = $this->mapEstadoEnumToDominioId($data);
        // Validar que no exista otra sesión activa para la sucursal y fecha
        $estadoActiva = \App\Enums\EstadoSesionEnum::ACTIVA->value;
        $fecha = isset($data['fecha']) ? date('Y-m-d', strtotime($data['fecha'])) : date('Y-m-d');
        $sucursalId = $data['fk_sucursal_id'] ?? null;
        if ($sucursalId && isset($data['fk_dominio_estado_id'])) {
            $dominioActiva = \App\Models\Dominio::where('nombre', $estadoActiva)->first();
            if ($dominioActiva && $data['fk_dominio_estado_id'] == $dominioActiva->dominio_id) {
                $existe = Sesion::where('fk_sucursal_id', $sucursalId)
                    ->whereDate('fecha', $fecha)
                    ->where('fk_dominio_estado_id', $dominioActiva->dominio_id)
                    ->exists();
                if ($existe) {
                    throw new \Exception('No se puede crear la sesión: ya existe una sesión activa para esta sucursal y fecha.');
                }
            }
        }

        // --- Lógica de cierre forzado y observación de ventanillas inconclusas ---
        $ayer = date('Y-m-d', strtotime($fecha . ' -1 day'));
        $dominioActiva = \App\Models\Dominio::where('nombre', \App\Enums\EstadoSesionEnum::ACTIVA->value)->first();
        $dominioCerrada = \App\Models\Dominio::where('nombre', \App\Enums\EstadoSesionEnum::CERRADA->value)->first();
        $dominioVentanillaActiva = \App\Enums\EstadoSesionVentanillaEnum::ACTIVA->value;
        $dominioVentanillaCerrada = \App\Enums\EstadoSesionVentanillaEnum::CERRADA->value;
        $observaciones = [];
        // Buscar sesión general activa del día anterior
        $sesionAyer = Sesion::where('fk_sucursal_id', $sucursalId)
            ->whereDate('fecha', $ayer)
            ->where('fk_dominio_estado_id', $dominioActiva ? $dominioActiva->dominio_id : null)
            ->first();
        if ($sesionAyer) {
            // Buscar ventanillas abiertas en esa sesión
            $ventanillasAyer = \App\Models\SesionVentanilla::where('fk_sesion_id', $sesionAyer->sesion_id)
                ->where('estado', $dominioVentanillaActiva)
                ->get();
            foreach ($ventanillasAyer as $sv) {
                // Forzar cierre de sesión de ventanilla
                $sv->estado = $dominioVentanillaCerrada;
                $sv->hora_cierre = now();
                $sv->save();
                // Buscar fichas inconclusas (en_espera, llamado, en_atencion)
                $fichasInconclusas = \App\Models\Ficha::where('fk_sesion_id', $sesionAyer->sesion_id)
                    ->whereHas('seguimientos', function($q) {
                        $q->whereIn('fk_dominio_estado_id', function($sub) {
                            $sub->select('dominio_id')
                                ->from('dominios')
                                ->whereIn('nombre', ['en_espera', 'llamado', 'en_atencion']);
                        });
                    })
                    ->get();
                if ($fichasInconclusas->count() > 0) {
                    $ventanilla = $sv->ventanilla;
                    $observaciones[] = 'Ventanilla #' . ($ventanilla ? $ventanilla->numero : $sv->fk_ventanilla_id) . ' dejó ' . $fichasInconclusas->count() . ' fichas inconclusas.';
                }
            }
            // Forzar cierre de sesión general
            $sesionAyer->fk_dominio_estado_id = $dominioCerrada ? $dominioCerrada->dominio_id : $sesionAyer->fk_dominio_estado_id;
            $sesionAyer->save();
        }

        // Crear la nueva sesión
        $nuevaSesion = Sesion::create($data);
        // Si hubo observaciones, registrar en la nueva sesión
        if (count($observaciones) > 0) {
            $nuevaSesion->observacion = implode(' | ', $observaciones);
            $nuevaSesion->save();
        }
        return $nuevaSesion;
    }

    public function actualizarSesion(Sesion $sesion, array $data): Sesion
    {
        $data = $this->mapEstadoEnumToDominioId($data);
        $sesion->update($data);
        return $sesion;
    }

    /**
     * Traduce el valor string de Enum a dominio_id para el campo estado
     */
    private function mapEstadoEnumToDominioId(array $data): array
    {
        if (isset($data['estado'])) {
            $dominio = Dominio::whereRaw('LOWER(nombre) = ?', [strtolower($data['estado'])])->first();
            if ($dominio) {
                $data['fk_dominio_estado_id'] = $dominio->dominio_id;
            }
            unset($data['estado']);
        }
        return $data;
    }
}
