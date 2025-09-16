<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\EstadoSeguimientoEnum;

class StoreSeguimientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Ahora se acepta el valor string de Enum en vez de ID para estado.
     * Ejemplo: estado: 'en_espera', 'llamado', 'atendido', 'finalizado'
     */
    public function rules()
    {
        return [
            'fk_ficha_id' => 'required|exists:fichas,ficha_id',
            'fk_ventanilla_id' => 'required|exists:ventanillas,ventanilla_id',
            'fk_usuario_id' => 'required|exists:usuarios,usuario_id',
            'estado' => 'required|string|in:' . implode(',', array_column(EstadoSeguimientoEnum::cases(), 'value')),
            'fk_dominio_accion_id' => 'required|exists:dominios,dominio_id',
            'fecha' => 'required|date',
            'observacion' => 'nullable|string',
        ];
    }
}
