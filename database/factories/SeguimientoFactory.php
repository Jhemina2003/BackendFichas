<?php

namespace Database\Factories;

use App\Models\Seguimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeguimientoFactory extends Factory
{
    protected $model = Seguimiento::class;

    public function definition()
    {
        return [
            'fk_ficha_id' => \App\Models\Ficha::factory(),
            'fk_ventanilla_id' => \App\Models\Ventanilla::factory(),
            'fk_usuario_id' => \App\Models\Usuario::factory(),
            'fk_dominio_estado_id' => \App\Models\Dominio::factory(),
            'fk_dominio_accion_id' => \App\Models\Dominio::factory(),
            'fecha' => now(),
            'observacion' => $this->faker->sentence,
        ];
    }
}
