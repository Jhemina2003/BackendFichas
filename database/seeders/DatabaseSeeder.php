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

        // Grupos de dominio específicos para el sistema de legalizaciones
        $tipoFichaGrupo = DominioGrupo::create(['nombre' => 'tipo_ficha']);
        $estadoSesionGrupo = DominioGrupo::create(['nombre' => 'estado_sesion']);
        $estadoSeguimientoGrupo = DominioGrupo::create(['nombre' => 'estado_seguimiento']);

    // Dominios para tipo_ficha
    $tipoNormal = Dominio::create(['nombre' => 'normal', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
    $tipoPreferencial = Dominio::create(['nombre' => 'preferencial', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
        
        
    // Dominios para prioridad_ficha
        
    // Dominios para estado_sesion
    $sesionActiva = Dominio::create(['nombre' => 'activa', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
    $sesionCerrada = Dominio::create(['nombre' => 'cerrada', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
        
    // Dominios para estado_seguimiento
    $seguimientoEspera = Dominio::create(['nombre' => 'en_espera', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoLlamado = Dominio::create(['nombre' => 'llamado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoEnAtencion = Dominio::create(['nombre' => 'en_atencion', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoFinalizado = Dominio::create(['nombre' => 'finalizado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoAusente = Dominio::create(['nombre' => 'ausente', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoReasignado = Dominio::create(['nombre' => 'reasignado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);

        // Organización principal
        $org = Organizacion::create(['nombre' => 'Unidad Apostilla y Legalizaciones']);

        // Sucursal Santa Cruz
        $sucursal = Sucursal::create(['fk_organizacion_id' => $org->organizacion_id, 'nombre' => 'Santa Cruz']);

        // Crear 10 ventanillas (inicialmente cerradas)
        $ventanillas = [];
        for ($i = 1; $i <= 10; $i++) {
            $ventanillas[$i] = Ventanilla::create([
                'fk_sucursal_id' => $sucursal->sucursal_id,
                'numero' => $i,
                'estado' => \App\Enums\EstadoVentanillaEnum::CERRADA->value
            ]);
        }

        // Crear 10 usuarios ventanilla con contraseña genérica 'ventanillaX123'
        for ($i = 1; $i <= 10; $i++) {
            Usuario::create([
                'fk_sucursal_id' => $sucursal->sucursal_id,
                // 'fk_dominio_tipo_servicio_id' => null, // Eliminado, ya no se usa
                'usuario' => 'ventanilla' . $i,
                'nombre_completo' => 'Ventanilla ' . $i,
                'correo_electronico' => 'ventanilla' . $i . '@ejemplo.com',
                'password' => bcrypt('ventanilla' . $i . '123'),
                'activo' => true,
                'fk_persona_id' => 100 + $i,
                'fk_ventanilla_id' => $ventanillas[$i]->ventanilla_id
            ]);
        }

        // Usuario extra 'fichas' (sin ventanilla asignada, contraseña: fichas123)
        Usuario::create([
            'fk_sucursal_id' => $sucursal->sucursal_id,
            // 'fk_dominio_tipo_servicio_id' => null, // Eliminado, ya no se usa
            'usuario' => 'fichas',
            'nombre_completo' => 'Usuario Fichas',
            'correo_electronico' => 'fichas@ejemplo.com',
            'password' => bcrypt('fichas123'),
            'activo' => true,
            'fk_persona_id' => 999,
            'fk_ventanilla_id' => null
        ]);
    }
}
