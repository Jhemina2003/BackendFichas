<?php

namespace Database\Factories;

use App\Models\Organizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizacionFactory extends Factory
{
    protected $model = Organizacion::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company,
            'sigla' => $this->faker->lexify('ORG??'),
        ];
    }
}
