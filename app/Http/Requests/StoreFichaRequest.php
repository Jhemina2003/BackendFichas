<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\TipoFichaEnum;
use App\Enums\TipoServicioEnum;
use App\Enums\PrioridadFichaEnum;

class StoreFichaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

        /**
         * Ahora se aceptan los valores string de Enum en vez de IDs para tipo_ficha, tipo_servicio y prioridad_ficha.
         * Ejemplo: tipo_ficha: 'normal', tipo_servicio: 'apostilla', prioridad_ficha: 'tercera_edad'
         */
        public function rules()
        {
            return [
                'fk_sucursal_id' => 'required|exists:sucursales,sucursal_id',
                'tipo_ficha' => 'required|string|in:' . implode(',', array_column(TipoFichaEnum::cases(), 'value')),
                'tipo_servicio' => 'required|string|in:' . implode(',', array_column(TipoServicioEnum::cases(), 'value')),
            ];
        }

    protected function prepareForValidation()
    {
    }

    public function withValidator($validator)
    {
    }
}
