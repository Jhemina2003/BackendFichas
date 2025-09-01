<?php

namespace Database\Factories;

use App\Models\Asignacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignacionFactory extends Factory
{
    protected $model = Asignacion::class;

    public function definition()
    {
        return [
            'fk_usuario_id' => \App\Models\Usuario::factory(),
            'fk_ventanilla_id' => \App\Models\Ventanilla::factory(),
            'fecha_inicio' => now(),
            'fecha_fin' => null,
        ];
    }
}
