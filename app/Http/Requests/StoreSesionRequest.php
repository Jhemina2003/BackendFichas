<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\EstadoSesionEnum;

class StoreSesionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Ahora se acepta el valor string de Enum en vez de ID para estado.
     * Ejemplo: estado: 'activa', 'cerrada'
     */
    public function rules()
    {
        return [
            'fk_sucursal_id' => 'sometimes|exists:sucursales,sucursal_id',
            'estado' => 'sometimes|string|in:' . implode(',', array_column(EstadoSesionEnum::cases(), 'value')),
            'fecha' => 'sometimes|date',
        ];
    }
}
