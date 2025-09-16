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
        $data['numero'] = $this->generarNumeroFicha($data);
        return Ficha::create($data);
    }
    /**
     * Genera el número correlativo de ficha con prefijo según el tipo de servicio y prioridad.
     */
    private function generarNumeroFicha(array $data): string
    {
        // Mapear tipo_servicio_id a prefijo
        $prefijos = [
            'apostilla' => 'APOS',
            'legalizaciones' => 'LEGAL',
            'vivencia' => 'VIVENCIA',
            'devoluciones' => 'DEV',
        ];
        $tipoServicioDominio = \App\Models\Dominio::find($data['fk_tipo_servicio_id']);
        $tipoServicioNombre = $tipoServicioDominio ? $tipoServicioDominio->nombre : '';
        $prefijo = $prefijos[$tipoServicioNombre] ?? 'FICHA';

        // Prefijo de prioridad
        $esPrioritaria = isset($data['fk_tipo_ficha_id']) && \App\Models\Dominio::find($data['fk_tipo_ficha_id'])->nombre === 'prioritaria';
        $prefijoFinal = $esPrioritaria ? 'P.' . $prefijo : $prefijo;

        // Buscar el último número correlativo para ese tipo y prioridad
        $query = \App\Models\Ficha::where('fk_tipo_servicio_id', $data['fk_tipo_servicio_id'])
            ->where('fk_tipo_ficha_id', $data['fk_tipo_ficha_id']);
        $ultimo = $query->orderByDesc('ficha_id')->first();
        $correlativo = 1;
        if ($ultimo && preg_match('/(\d+)$/', $ultimo->numero, $m)) {
            $correlativo = intval($m[1]) + 1;
        }
        return $prefijoFinal . '.' . $correlativo;
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
            $data['numero'] = $this->generarNumeroFicha($nuevoData);
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
