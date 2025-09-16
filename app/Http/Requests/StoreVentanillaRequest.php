<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentanillaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_sucursal_id' => 'required|exists:sucursales,sucursal_id',
            'numero' => 'required|integer',
            'bloqueado' => 'boolean',
        ];
    }
}
