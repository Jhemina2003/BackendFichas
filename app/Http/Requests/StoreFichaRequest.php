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
                'prioridad_ficha' => 'required_if:tipo_ficha,prioritaria|string|in:' . implode(',', array_column(PrioridadFichaEnum::cases(), 'value')),
            ];
        }

    protected function prepareForValidation()
    {
        // Si tipo_ficha es normal y viene prioridad_ficha, eliminar prioridad_ficha antes de validar
        if ($this->input('tipo_ficha') === 'normal' && $this->has('prioridad_ficha')) {
            $this->merge(['prioridad_ficha' => null]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('tipo_ficha') === 'normal' && $this->filled('prioridad_ficha')) {
                $validator->errors()->add('prioridad_ficha', 'No se puede asignar prioridad a una ficha de tipo normal.');
            }
        });
    }
}
