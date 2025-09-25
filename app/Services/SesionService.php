<?php

namespace App\Services;

use App\Models\Sesion;
use App\Models\Dominio;

class SesionService
{
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
                    throw new \Exception('Ya existe una sesión activa para esta sucursal y fecha.');
                }
            }
        }
        return Sesion::create($data);
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
