<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fk_sucursal_id' => 'sometimes|exists:sucursales,sucursal_id',
            'fk_dominio_tipo_servicio_id' => 'sometimes|exists:dominios,dominio_id',
            'usuario' => 'sometimes|string|max:255',
            'nombre_completo' => 'sometimes|string|max:255',
            'correo_electronico' => 'sometimes|email|max:255',
            'fk_persona_id' => 'sometimes|integer',
            'activo' => 'sometimes|boolean',
        ];
    }
}
