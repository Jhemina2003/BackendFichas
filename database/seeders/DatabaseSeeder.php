<?php

namespace Database\Seeders;

use App\Models\Dominio;
use App\Models\DominioGrupo;
use App\Models\Ficha;
use App\Models\Llamada;
use App\Models\Organizacion;
use App\Models\Rol;
use App\Models\RolUsuario;
use App\Models\Seguimiento;
use App\Models\Sesion;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\Ventanilla;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Organizaciones
        $org = Organizacion::create(['nombre' => 'Banco Central']);

    // Grupos de dominio (solo los necesarios)
    $tipoFichaGrupo = DominioGrupo::create(['nombre' => 'tipo_ficha']);
    $tipoServicioGrupo = DominioGrupo::create(['nombre' => 'tipo_servicio']);
    $estadoSesionGrupo = DominioGrupo::create(['nombre' => 'estado_sesion']);
    $estadoSeguimientoGrupo = DominioGrupo::create(['nombre' => 'estado_seguimiento']);

        // Dominios para tipo_ficha
        $tipoFichaA = Dominio::create(['nombre' => 'Normal', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
        $tipoFichaB = Dominio::create(['nombre' => 'Preferencial', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
        
        // Dominios para tipo_servicio
        $servicioVentanilla = Dominio::create(['nombre' => 'Ventanilla', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
        $servicioPlataforma = Dominio::create(['nombre' => 'Plataforma', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
        
        // Dominios para estado_sesion
        $sesionActiva = Dominio::create(['nombre' => 'Activa', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
        $sesionCerrada = Dominio::create(['nombre' => 'Cerrada', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
        
        // Dominios para estado_seguimiento
        $seguimientoEspera = Dominio::create(['nombre' => 'En Espera', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
        $seguimientoAtendido = Dominio::create(['nombre' => 'Atendido', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);

        // Sucursal
        $sucursal = Sucursal::create(['fk_organizacion_id' => $org->organizacion_id, 'nombre' => 'Sucursal Centro']);

        // Ventanillas
        $ventanilla1 = Ventanilla::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'numero' => 1, 'bloqueado' => false]);
        $ventanilla2 = Ventanilla::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'numero' => 2, 'bloqueado' => false]);

        // Roles
        $rolCajero = Rol::create(['nombre' => 'Cajero']);
        $rolSupervisor = Rol::create(['nombre' => 'Supervisor']);

        // Sesión
        $sesion = Sesion::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'fk_dominio_estado_id' => $sesionActiva->dominio_id, 'fecha' => now()]);

        // Usuarios
        $usuario1 = Usuario::create([
            'fk_sucursal_id' => $sucursal->sucursal_id,
            'fk_dominio_tipo_servicio_id' => $servicioVentanilla->dominio_id,
            'usuario' => 'jlopez',
            'nombre_completo' => 'Juan Lopez',
            'correo_electronico' => 'jlopez@banco.com',
            'activo' => true,
            'fk_persona_id' => 1
        ]);
        $usuario2 = Usuario::create([
            'fk_sucursal_id' => $sucursal->sucursal_id,
            'fk_dominio_tipo_servicio_id' => $servicioPlataforma->dominio_id,
            'usuario' => 'mperez',
            'nombre_completo' => 'Maria Perez',
            'correo_electronico' => 'mperez@banco.com',
            'activo' => true,
            'fk_persona_id' => 2
        ]);

        // Fichas
        $ficha1 = Ficha::create([
            'fk_sesion_id' => $sesion->sesion_id,
            'fk_dominio_tipo_id' => $tipoFichaA->dominio_id,
            'numero' => 101,
            'fecha_inicio' => now(),
            'fecha_registro' => now(),
            'cantidad_llamadas' => 0
        ]);
        $ficha2 = Ficha::create([
            'fk_sesion_id' => $sesion->sesion_id,
            'fk_dominio_tipo_id' => $tipoFichaB->dominio_id,
            'numero' => 102,
            'fecha_inicio' => now(),
            'fecha_registro' => now(),
            'cantidad_llamadas' => 0
        ]);

        // Llamadas
        $llamada1 = Llamada::create([
            'fk_usuario_id' => $usuario1->usuario_id,
            'fk_ventanilla_id' => $ventanilla1->ventanilla_id,
            'fk_ficha_id' => $ficha1->ficha_id,
            'fecha' => now()
        ]);

        // RolUsuario
        RolUsuario::create([
            'fk_usuario_id' => $usuario1->usuario_id,
            'fk_rol_id' => $rolCajero->rol_id
        ]);
        RolUsuario::create([
            'fk_usuario_id' => $usuario2->usuario_id,
            'fk_rol_id' => $rolSupervisor->rol_id
        ]);

        // Seguimiento
        Seguimiento::create([
            'fk_ficha_id' => $ficha1->ficha_id,
            'fk_ventanilla_id' => $ventanilla1->ventanilla_id,
            'fk_usuario_id' => $usuario1->usuario_id,
            'fk_dominio_estado_id' => $seguimientoEspera->dominio_id,
            'fk_dominio_accion_id' => $seguimientoEspera->dominio_id,
            'fecha' => now(),
            'observacion' => 'Ficha llamada a ventanilla 1.'
        ]);
    }
}
