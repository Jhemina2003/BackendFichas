<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\EstadoSesionEnum;

class UpdateSesionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_sucursal_id' => 'sometimes|exists:sucursales,sucursal_id',
            'estado' => 'sometimes|string|in:' . implode(',', array_column(EstadoSesionEnum::cases(), 'value')),
            'fecha' => 'sometimes|date',
        ];
    }
}
