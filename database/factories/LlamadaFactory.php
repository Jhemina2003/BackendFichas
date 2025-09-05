<?php

namespace Database\Factories;

use App\Models\Llamada;
use Illuminate\Database\Eloquent\Factories\Factory;

class LlamadaFactory extends Factory
{
    protected $model = Llamada::class;

    public function definition()
    {
        return [
            'fk_usuario_id' => \App\Models\Usuario::factory(),
            'fk_ventanilla_id' => \App\Models\Ventanilla::factory(),
            'fk_ficha_id' => null,
            'fecha' => now(),
        ];
    }
}
