<?php

namespace Database\Factories;

use App\Models\Ventanilla;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentanillaFactory extends Factory
{
    protected $model = Ventanilla::class;

    public function definition()
    {
        return [
            'fk_sucursal_id' => \App\Models\Sucursal::factory(),
            'numero' => $this->faker->randomDigitNotNull,
            'estado' => 'abierta',
        ];
    }
}
