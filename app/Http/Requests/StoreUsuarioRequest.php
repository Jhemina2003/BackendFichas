<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_sucursal_id' => 'required|exists:sucursales,sucursal_id',
            'fk_dominio_tipo_servicio_id' => 'required|exists:dominios,dominio_id',
            'usuario' => 'required|string|max:255',
            'nombre_completo' => 'required|string|max:255',
            'correo_electronico' => 'required|email|max:255',
            'fk_persona_id' => 'required|integer',
            'activo' => 'boolean',
        ];
    }
}
