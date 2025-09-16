<?php

namespace App\Services;

use App\Models\Usuario;

class UsuarioService
{
    public function crearUsuario(array $data): Usuario
    {
        return Usuario::create($data);
    }

    public function actualizarUsuario(Usuario $usuario, array $data): Usuario
    {
        $usuario->update($data);
        return $usuario;
    }
}
