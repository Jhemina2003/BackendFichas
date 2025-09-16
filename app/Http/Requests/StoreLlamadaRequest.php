<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLlamadaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_ficha_id' => 'required|exists:fichas,ficha_id',
            'fecha' => 'required|date',
        ];
    }
}
