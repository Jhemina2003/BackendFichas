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
         * Los valores string de Enum se aceptan para tipo_ficha y tipo_servicio.
         * La sucursal se obtiene automáticamente del usuario autenticado.
         * Ejemplo: tipo_ficha: 'normal', tipo_servicio: 'apostilla'
         */
        public function rules()
        {
            return [
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
