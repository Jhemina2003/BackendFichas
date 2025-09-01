<?php

namespace Database\Factories;

use App\Models\RolUsuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolUsuarioFactory extends Factory
{
    protected $model = RolUsuario::class;

    public function definition()
    {
        return [
            'fk_usuario_id' => \App\Models\Usuario::factory(),
            'fk_rol_id' => \App\Models\Rol::factory(),
        ];
    }
}
