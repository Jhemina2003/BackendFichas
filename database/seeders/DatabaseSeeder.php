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
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Grupos de dominio específicos para el sistema de legalizaciones
        $tipoFichaGrupo = DominioGrupo::firstOrCreate(['nombre' => 'tipo_ficha']);
        $tipoServicioGrupo = DominioGrupo::firstOrCreate(['nombre' => 'tipo_servicio']);
        $estadoSesionGrupo = DominioGrupo::firstOrCreate(['nombre' => 'estado_sesion']);
        $estadoSeguimientoGrupo = DominioGrupo::firstOrCreate(['nombre' => 'estado_seguimiento']);

        // Dominios para tipo_ficha
        $tipoNormal = Dominio::firstOrCreate(['nombre' => 'normal', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
        $tipoPreferencial = Dominio::firstOrCreate(['nombre' => 'preferencial', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);

        // Dominios para tipo_servicio
        $servicioApostilla = Dominio::firstOrCreate(['nombre' => 'apostilla', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
        $servicioLegalizaciones = Dominio::firstOrCreate(['nombre' => 'legalizaciones', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
        $servicioVivencia = Dominio::firstOrCreate(['nombre' => 'vivencia', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
        $servicioDevoluciones = Dominio::firstOrCreate(['nombre' => 'devoluciones', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);

        // Dominios para estado_sesion
        $sesionActiva = Dominio::firstOrCreate(['nombre' => 'activa', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
        $sesionCerrada = Dominio::firstOrCreate(['nombre' => 'cerrada', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);

        // Dominios para estado_seguimiento
        $seguimientoEspera = Dominio::firstOrCreate(['nombre' => 'en_espera', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
        $seguimientoLlamado = Dominio::firstOrCreate(['nombre' => 'llamado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
        $seguimientoEnAtencion = Dominio::firstOrCreate(['nombre' => 'en_atencion', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
        $seguimientoFinalizado = Dominio::firstOrCreate(['nombre' => 'finalizado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
        $seguimientoAusente = Dominio::firstOrCreate(['nombre' => 'ausente', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);

        // Eliminar todas las ventanillas, sucursales y organizaciones
        \App\Models\Ventanilla::truncate();
        \App\Models\Sucursal::truncate();
        \App\Models\Organizacion::truncate();

        // Eliminar todos los usuarios de prueba
        \App\Models\Usuario::truncate();

        // Crear roles del sistema
        \App\Models\Rol::firstOrCreate(['nombre' => 'Administrador']);
        \App\Models\Rol::firstOrCreate(['nombre' => 'Ventanilla']);
        \App\Models\Rol::firstOrCreate(['nombre' => 'Fichas']);

        // Los usuarios deben ser asignados a roles manualmente por base de datos

        // No crear ventanillas, sucursales ni organizaciones por seed
    }
}
