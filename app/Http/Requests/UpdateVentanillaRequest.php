<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVentanillaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_sucursal_id' => 'sometimes|exists:sucursales,sucursal_id',
            'numero' => 'sometimes|integer',
            'bloqueado' => 'sometimes|boolean',
        ];
    }
}
