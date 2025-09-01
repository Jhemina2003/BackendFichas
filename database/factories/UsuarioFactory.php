<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition()
    {
        return [
            'usuario' => $this->faker->userName,
            'nombre_completo' => $this->faker->name,
            'correo_electronico' => $this->faker->unique()->safeEmail,
            'activo' => true,
            'fk_sucursal_id' => \App\Models\Sucursal::factory(),
            'fk_dominio_tipo_servicio_id' => \App\Models\Dominio::factory(),
            'fk_persona_id' => $this->faker->randomNumber(5),
        ];
    }
}
