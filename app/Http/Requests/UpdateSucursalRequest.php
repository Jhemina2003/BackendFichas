<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSucursalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_organizacion_id' => 'sometimes|exists:organizaciones,organizacion_id',
            'nombre' => 'sometimes|string|max:255',
        ];
    }
}
