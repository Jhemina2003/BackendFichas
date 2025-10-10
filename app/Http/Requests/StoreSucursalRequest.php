<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSucursalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_organizacion_id' => 'required|exists:organizaciones,organizacion_id',
            'nombre' => 'required|string|max:255|unique:sucursales,nombre,NULL,id,fk_organizacion_id,' . $this->fk_organizacion_id,
        ];
    }
}
