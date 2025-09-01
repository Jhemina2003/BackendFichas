<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition()
    {
        return [
            'fk_organizacion_id' => \App\Models\Organizacion::factory(),
            'nombre' => $this->faker->companySuffix,
        ];
    }
}
