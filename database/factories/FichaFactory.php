<?php

namespace Database\Factories;

use App\Models\Ficha;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaFactory extends Factory
{
    protected $model = Ficha::class;

    public function definition()
    {
        return [
            'fk_sesion_id' => \App\Models\Sesion::factory(),
            'fk_dominio_tipo_id' => \App\Models\Dominio::factory(),
            'fk_dominio_prioridad_id' => \App\Models\Dominio::factory(),
            'fk_llamada_id' => \App\Models\Llamada::factory(),
            'numero' => $this->faker->randomNumber(4),
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'fecha_registro' => now(),
            'cantidad_llamadas' => 0,
        ];
    }
}
