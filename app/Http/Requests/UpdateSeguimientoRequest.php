<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\EstadoSeguimientoEnum;

class UpdateSeguimientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_ficha_id' => 'sometimes|exists:fichas,ficha_id',
            'fk_ventanilla_id' => 'sometimes|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'sometimes|exists:usuarios,usuario_id',
            'estado' => 'sometimes|string|in:' . implode(',', array_column(EstadoSeguimientoEnum::cases(), 'value')),
            'fk_dominio_accion_id' => 'sometimes|exists:dominios,dominio_id',
            'fecha' => 'sometimes|date',
            'observacion' => 'nullable|string',
        ];
    }
}
