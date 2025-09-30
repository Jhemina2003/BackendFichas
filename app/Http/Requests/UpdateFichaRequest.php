<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\TipoFichaEnum;
use App\Enums\TipoServicioEnum;
use App\Enums\PrioridadFichaEnum;

class UpdateFichaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_sesion_id' => 'sometimes|exists:sesiones,sesion_id',
            'tipo_ficha' => 'sometimes|string|in:' . implode(',', array_column(TipoFichaEnum::cases(), 'value')),
            'tipo_servicio' => 'sometimes|string|in:' . implode(',', array_column(TipoServicioEnum::cases(), 'value')),
            'fecha_inicio' => 'sometimes|date',
            'fecha_registro' => 'sometimes|date',
            'cantidad_llamadas' => 'sometimes|integer',
            'prioridad_ficha' => 'required_if:tipo_ficha,preferencial|string|in:' . implode(',', array_column(PrioridadFichaEnum::cases(), 'value')),
        ];
    }
}
