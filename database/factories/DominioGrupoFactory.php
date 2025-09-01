<?php

namespace Database\Factories;

use App\Models\DominioGrupo;
use Illuminate\Database\Eloquent\Factories\Factory;

class DominioGrupoFactory extends Factory
{
    protected $model = DominioGrupo::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word,
        ];
    }
}
