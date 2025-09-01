<?php

namespace Database\Factories;

use App\Models\Sesion;
use Illuminate\Database\Eloquent\Factories\Factory;

class SesionFactory extends Factory
{
    protected $model = Sesion::class;

    public function definition()
    {
        return [
            'fk_sucursal_id' => \App\Models\Sucursal::factory(),
            'fk_dominio_estado_id' => \App\Models\Dominio::factory(),
            'fecha' => now(),
        ];
    }
}
