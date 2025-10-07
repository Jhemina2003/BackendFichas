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
        $seguimientoReasignado = Dominio::firstOrCreate(['nombre' => 'reasignado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);

        // Organización principal
        $org = Organizacion::firstOrCreate(['nombre' => 'Unidad Apostilla y Legalizaciones']);

        // Sucursal Santa Cruz
        $sucursal = Sucursal::firstOrCreate(['fk_organizacion_id' => $org->organizacion_id, 'nombre' => 'Santa Cruz']);

        // Crear 10 ventanillas (inicialmente cerradas)
        $ventanillas = [];
        for ($i = 1; $i <= 10; $i++) {
            $ventanillas[$i] = Ventanilla::firstOrCreate([
                'fk_sucursal_id' => $sucursal->sucursal_id,
                'numero' => $i,
                'estado' => \App\Enums\EstadoVentanillaEnum::CERRADA->value
            ]);
        }

        // Crear 10 usuarios ventanilla con contraseña genérica 'ventanillaX123'
        for ($i = 1; $i <= 10; $i++) {
            Usuario::updateOrCreate(
                ['usuario' => 'ventanilla' . $i],
                [
                    'fk_sucursal_id' => $sucursal->sucursal_id,
                    'nombre_completo' => 'Ventanilla ' . $i,
                    'correo_electronico' => 'ventanilla' . $i . '@ejemplo.com',
                    'password' => bcrypt('ventanilla' . $i . '123'),
                    'activo' => true,
                    'fk_persona_id' => 100 + $i,
                    'fk_ventanilla_id' => $ventanillas[$i]->ventanilla_id
                ]
            );
        }

        // Usuario extra 'fichas' (sin ventanilla asignada, contraseña: fichas123)
        Usuario::updateOrCreate(
            ['usuario' => 'fichas'],
            [
                'fk_sucursal_id' => $sucursal->sucursal_id, // Santa Cruz
                'nombre_completo' => 'Usuario Fichas',
                'correo_electronico' => 'fichas@ejemplo.com',
                'password' => bcrypt('fichas123'),
                'activo' => true,
                'fk_persona_id' => 999,
                'fk_ventanilla_id' => null
            ]
        );

        // Sucursal La Paz
        $sucursalLaPaz = Sucursal::firstOrCreate(['fk_organizacion_id' => $org->organizacion_id, 'nombre' => 'La Paz']);

        // Crear 5 ventanillas para La Paz (inicialmente cerradas)
        $ventanillasLaPaz = [];
        for ($i = 1; $i <= 5; $i++) {
            $ventanillasLaPaz[$i] = Ventanilla::firstOrCreate([
                'fk_sucursal_id' => $sucursalLaPaz->sucursal_id,
                'numero' => $i,
                'estado' => \App\Enums\EstadoVentanillaEnum::CERRADA->value
            ]);
        }

        // Crear 5 usuarios ventanilla para La Paz con contraseña genérica 'lapazX123'
        for ($i = 1; $i <= 5; $i++) {
            Usuario::updateOrCreate(
                ['usuario' => 'lapaz' . $i],
                [
                    'fk_sucursal_id' => $sucursalLaPaz->sucursal_id,
                    'nombre_completo' => 'Ventanilla La Paz ' . $i,
                    'correo_electronico' => 'lapaz' . $i . '@ejemplo.com',
                    'password' => bcrypt('lapaz' . $i . '123'),
                    'activo' => true,
                    'fk_persona_id' => 200 + $i,
                    'fk_ventanilla_id' => $ventanillasLaPaz[$i]->ventanilla_id
                ]
            );
        }

        // Usuario extra 'fichaslapaz' para La Paz (sin ventanilla asignada, contraseña: fichaslapaz123)
        Usuario::updateOrCreate(
            ['usuario' => 'fichaslapaz'],
            [
                'fk_sucursal_id' => $sucursalLaPaz->sucursal_id, // La Paz
                'nombre_completo' => 'Usuario Fichas La Paz',
                'correo_electronico' => 'fichaslapaz@ejemplo.com',
                'password' => bcrypt('fichaslapaz123'),
                'activo' => true,
                'fk_persona_id' => 299,
                'fk_ventanilla_id' => null
            ]
        );

        // Asignar servicios a ventanillas según la lógica definida
        $this->call(\Database\Seeders\VentanillaTipoServicioSeeder::class);
    }
}
