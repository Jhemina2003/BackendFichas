<?php

namespace Database\Factories;

use App\Models\Dominio;
use Illuminate\Database\Eloquent\Factories\Factory;

class DominioFactory extends Factory
{
    protected $model = Dominio::class;

    public function definition()
    {
        return [
            'fk_dominio_grupo_id' => \App\Models\DominioGrupo::factory(),
            'nombre' => $this->faker->word,
        ];
    }
}
