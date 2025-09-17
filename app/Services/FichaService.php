<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Dominio;

class FichaService
{
    /**
     * Lógica de negocio para crear una ficha (puedes expandir según reglas del sistema)
     */
    public function crearFicha(array $data): Ficha
    {
        $data = $this->mapEnumsToDominioIds($data);
        // Inicializar cantidad_llamadas en 0
        $data['cantidad_llamadas'] = 0;

        // Asignar fechas automáticamente
        $now = now();
        $data['fecha_registro'] = $now;
        $data['fecha_inicio'] = $now;

        // Asignar sesión automáticamente si no viene en el request
        if (empty($data['fk_sesion_id']) && !empty($data['fk_sucursal_id'])) {
            // Buscar dominio 'activa' para estado de sesión
            $dominioActiva = \App\Models\Dominio::where('nombre', 'activa')->first();
            $sesion = \App\Models\Sesion::where('fk_sucursal_id', $data['fk_sucursal_id'])
                ->whereDate('fecha', $now->toDateString())
                ->where('fk_dominio_estado_id', $dominioActiva ? $dominioActiva->dominio_id : null)
                ->orderByDesc('fecha')
                ->first();
            if ($sesion) {
                $data['fk_sesion_id'] = $sesion->sesion_id;
            } else {
                // Crear nueva sesión activa para la sucursal y día
                $sesion = \App\Models\Sesion::create([
                    'fk_sucursal_id' => $data['fk_sucursal_id'],
                    'fecha' => $now,
                    'hora_inicio' => $now,
                    'fk_dominio_estado_id' => $dominioActiva ? $dominioActiva->dominio_id : null,
                ]);
                $data['fk_sesion_id'] = $sesion->sesion_id;
            }
        }

        // Eliminar fk_sucursal_id porque no existe en la tabla fichas
        unset($data['fk_sucursal_id']);

        $data['numero'] = $this->generarCorrelativoFicha($data);
        return Ficha::create($data);
    }
    /**
     * Genera el número correlativo (integer) de ficha para el día, tipo y prioridad.
     */
    private function generarCorrelativoFicha(array $data): int
    {
        $fecha = isset($data['fecha_registro']) ? date('Y-m-d', strtotime($data['fecha_registro'])) : date('Y-m-d');
        // El correlativo es independiente para cada combinación de tipo de ficha y tipo de servicio, por día
        $maxNumero = Ficha::where('fk_tipo_ficha_id', $data['fk_tipo_ficha_id'])
            ->where('fk_tipo_servicio_id', $data['fk_tipo_servicio_id'])
            ->whereDate('fecha_registro', $fecha)
            ->max('numero');
        return $maxNumero ? ($maxNumero + 1) : 1;
    }

    /**
     * Lógica para actualizar una ficha
     */
    public function actualizarFicha(Ficha $ficha, array $data): Ficha
    {
        $data = $this->mapEnumsToDominioIds($data);
        // Si cambia tipo_ficha o tipo_servicio, regenerar el número correlativo
        $cambioTipo = isset($data['fk_tipo_ficha_id']) && $data['fk_tipo_ficha_id'] != $ficha->fk_tipo_ficha_id;
        $cambioServicio = isset($data['fk_tipo_servicio_id']) && $data['fk_tipo_servicio_id'] != $ficha->fk_tipo_servicio_id;
        if ($cambioTipo || $cambioServicio) {
            $nuevoData = array_merge($ficha->toArray(), $data);
            $data['numero'] = $this->generarCorrelativoFicha($nuevoData);
        }
        $ficha->update($data);
        return $ficha;
    }

    /**
     * Traduce los valores string de Enum a dominio_id para los campos tipo_ficha, tipo_servicio y prioridad_ficha
     */
    private function mapEnumsToDominioIds(array $data): array
    {
        if (isset($data['tipo_ficha'])) {
            $dominio = Dominio::where('nombre', $data['tipo_ficha'])->first();
            if ($dominio) {
                $data['fk_tipo_ficha_id'] = $dominio->dominio_id;
                $data['fk_dominio_tipo_id'] = $dominio->dominio_id;
            }
            // Si la ficha es normal, eliminar prioridad_ficha (no debe tener prioridad)
            if ($data['tipo_ficha'] === 'normal') {
                unset($data['prioridad_ficha']);
                $data['fk_prioridad_ficha_id'] = null;
            }
            unset($data['tipo_ficha']);
        }
        if (isset($data['tipo_servicio'])) {
            $dominio = Dominio::where('nombre', $data['tipo_servicio'])->first();
            if ($dominio) {
                $data['fk_tipo_servicio_id'] = $dominio->dominio_id;
            }
            unset($data['tipo_servicio']);
        }
        if (isset($data['prioridad_ficha'])) {
            $dominio = Dominio::where('nombre', $data['prioridad_ficha'])->first();
            if ($dominio) {
                $data['fk_prioridad_ficha_id'] = $dominio->dominio_id;
            }
            unset($data['prioridad_ficha']);
        }
        return $data;
    }
}
