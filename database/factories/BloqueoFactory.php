<?php

namespace Database\Factories;

use App\Models\Bloqueo;
use Illuminate\Database\Eloquent\Factories\Factory;

class BloqueoFactory extends Factory
{
    protected $model = Bloqueo::class;

    public function definition()
    {
        return [
            'fk_ventanilla_id' => \App\Models\Ventanilla::factory(),
            'fk_horario_id' => \App\Models\Horario::factory(),
            'fk_usuario_id' => \App\Models\Usuario::factory(),
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'motivo' => $this->faker->sentence,
        ];
    }
}
