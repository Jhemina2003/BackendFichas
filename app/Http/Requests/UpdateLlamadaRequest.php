<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLlamadaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_usuario_id' => 'sometimes|exists:usuarios,usuario_id',
            'fk_ventanilla_id' => 'sometimes|exists:ventanillas,ventanilla_id',
            'fk_ficha_id' => 'sometimes|exists:fichas,ficha_id',
            'fecha' => 'sometimes|date',
        ];
    }
}
