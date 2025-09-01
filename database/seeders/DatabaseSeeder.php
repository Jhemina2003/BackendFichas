<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Organizacion;
use App\Models\Sucursal;
use App\Models\Horario;
use App\Models\Ventanilla;
use App\Models\Rol;
use App\Models\DominioGrupo;
use App\Models\Dominio;
use App\Models\Ficha;
use App\Models\Llamada;
use App\Models\Asignacion;
use App\Models\RolUsuario;
use App\Models\Sesion;
use App\Models\Seguimiento;
use App\Models\Bloqueo;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear datos base primero (menos cantidad para evitar problemas)
        Organizacion::factory(2)->create();
        DominioGrupo::factory(3)->create();
        Dominio::factory(5)->create();
        Sucursal::factory(2)->create();
        Horario::factory(3)->create();
        Ventanilla::factory(3)->create();
        Rol::factory(3)->create();
        
        // Crear sesiones y usuarios
        Sesion::factory(3)->create();
        Usuario::factory(5)->create();
        
        // Crear fichas y resto de tablas
        Ficha::factory(5)->create();
        Llamada::factory(5)->create();
        Asignacion::factory(3)->create();
        RolUsuario::factory(5)->create();
        Seguimiento::factory(5)->create();
        Bloqueo::factory(3)->create();
    }
}
