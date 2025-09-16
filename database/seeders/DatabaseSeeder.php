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
        $org = Organizacion::create(['nombre' => 'Ministerio de Relaciones Exteriores']);

        // Grupos de dominio específicos para el sistema de legalizaciones
        $tipoFichaGrupo = DominioGrupo::create(['nombre' => 'tipo_ficha']);
        $tipoServicioGrupo = DominioGrupo::create(['nombre' => 'tipo_servicio']);
        $prioridadFichaGrupo = DominioGrupo::create(['nombre' => 'prioridad_ficha']);
        $estadoSesionGrupo = DominioGrupo::create(['nombre' => 'estado_sesion']);
        $estadoSeguimientoGrupo = DominioGrupo::create(['nombre' => 'estado_seguimiento']);

    // Dominios para tipo_ficha
    $tipoNormal = Dominio::create(['nombre' => 'normal', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
    $tipoPrioridad = Dominio::create(['nombre' => 'prioritaria', 'fk_dominio_grupo_id' => $tipoFichaGrupo->dominio_grupo_id]);
        
    // Dominios para tipo_servicio (servicios del ministerio)
    $servicioApostilla = Dominio::create(['nombre' => 'apostilla', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
    $servicioLegalizaciones = Dominio::create(['nombre' => 'legalizaciones', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
    $servicioVivencia = Dominio::create(['nombre' => 'vivencia', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
    $servicioDevoluciones = Dominio::create(['nombre' => 'devoluciones', 'fk_dominio_grupo_id' => $tipoServicioGrupo->dominio_grupo_id]);
        
    // Dominios para prioridad_ficha
    $prioridadEmbarazada = Dominio::create(['nombre' => 'embarazada', 'fk_dominio_grupo_id' => $prioridadFichaGrupo->dominio_grupo_id]);
    $prioridadTerceraEdad = Dominio::create(['nombre' => 'tercera_edad', 'fk_dominio_grupo_id' => $prioridadFichaGrupo->dominio_grupo_id]);
    $prioridadDiscapacidad = Dominio::create(['nombre' => 'discapacidad', 'fk_dominio_grupo_id' => $prioridadFichaGrupo->dominio_grupo_id]);
        
    // Dominios para estado_sesion
    $sesionActiva = Dominio::create(['nombre' => 'activa', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
    $sesionCerrada = Dominio::create(['nombre' => 'cerrada', 'fk_dominio_grupo_id' => $estadoSesionGrupo->dominio_grupo_id]);
        
    // Dominios para estado_seguimiento
    $seguimientoEspera = Dominio::create(['nombre' => 'en_espera', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoLlamado = Dominio::create(['nombre' => 'llamado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoAtendido = Dominio::create(['nombre' => 'atendido', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoFinalizado = Dominio::create(['nombre' => 'finalizado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);
    $seguimientoCancelado = Dominio::create(['nombre' => 'cancelado', 'fk_dominio_grupo_id' => $estadoSeguimientoGrupo->dominio_grupo_id]);

        // Sucursal
        $sucursal = Sucursal::create(['fk_organizacion_id' => $org->organizacion_id, 'nombre' => 'Sucursal La Paz']);

        // Ventanillas para diferentes servicios
        $ventanilla1 = Ventanilla::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'numero' => 1, 'bloqueado' => false]);
        $ventanilla2 = Ventanilla::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'numero' => 2, 'bloqueado' => false]);
        $ventanilla3 = Ventanilla::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'numero' => 3, 'bloqueado' => false]);

        // Roles
        $rolOperador = Rol::create(['nombre' => 'Operador']);
        $rolSupervisor = Rol::create(['nombre' => 'Supervisor']);
        $rolAdministrador = Rol::create(['nombre' => 'Administrador']);

        // Sesión activa
        $sesion = Sesion::create(['fk_sucursal_id' => $sucursal->sucursal_id, 'fk_dominio_estado_id' => $sesionActiva->dominio_id, 'fecha' => now()]);

        // Usuarios
        $usuario1 = Usuario::create([
            'fk_sucursal_id' => $sucursal->sucursal_id,
            'fk_dominio_tipo_servicio_id' => $servicioApostilla->dominio_id,
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Pérez',
            'correo_electronico' => 'jperez@rree.gob.bo',
            'activo' => true,
            'fk_persona_id' => 1
        ]);
        $usuario2 = Usuario::create([
            'fk_sucursal_id' => $sucursal->sucursal_id,
            'fk_dominio_tipo_servicio_id' => $servicioLegalizaciones->dominio_id,
            'usuario' => 'mlopez',
            'nombre_completo' => 'María López',
            'correo_electronico' => 'mlopez@rree.gob.bo',
            'activo' => true,
            'fk_persona_id' => 2
        ]);

        // Fichas de ejemplo cubriendo todas las combinaciones
        $fichas = [
            // Fichas normales (sin prioridad)
            [
                'tipo' => $tipoNormal->dominio_id,
                'servicio' => $servicioApostilla->dominio_id,
                'prioridad' => null,
                'numero' => 1
            ],
            [
                'tipo' => $tipoNormal->dominio_id,
                'servicio' => $servicioLegalizaciones->dominio_id,
                'prioridad' => null,
                'numero' => 2
            ],
            [
                'tipo' => $tipoNormal->dominio_id,
                'servicio' => $servicioVivencia->dominio_id,
                'prioridad' => null,
                'numero' => 3
            ],
            [
                'tipo' => $tipoNormal->dominio_id,
                'servicio' => $servicioDevoluciones->dominio_id,
                'prioridad' => null,
                'numero' => 4
            ],
            // Fichas con prioridad: Embarazada
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioApostilla->dominio_id,
                'prioridad' => $prioridadEmbarazada->dominio_id,
                'numero' => 5
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioLegalizaciones->dominio_id,
                'prioridad' => $prioridadEmbarazada->dominio_id,
                'numero' => 6
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioVivencia->dominio_id,
                'prioridad' => $prioridadEmbarazada->dominio_id,
                'numero' => 7
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioDevoluciones->dominio_id,
                'prioridad' => $prioridadEmbarazada->dominio_id,
                'numero' => 8
            ],
            // Fichas con prioridad: Tercera Edad
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioApostilla->dominio_id,
                'prioridad' => $prioridadTerceraEdad->dominio_id,
                'numero' => 9
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioLegalizaciones->dominio_id,
                'prioridad' => $prioridadTerceraEdad->dominio_id,
                'numero' => 10
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioVivencia->dominio_id,
                'prioridad' => $prioridadTerceraEdad->dominio_id,
                'numero' => 11
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioDevoluciones->dominio_id,
                'prioridad' => $prioridadTerceraEdad->dominio_id,
                'numero' => 12
            ],
            // Fichas con prioridad: Discapacidad
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioApostilla->dominio_id,
                'prioridad' => $prioridadDiscapacidad->dominio_id,
                'numero' => 13
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioLegalizaciones->dominio_id,
                'prioridad' => $prioridadDiscapacidad->dominio_id,
                'numero' => 14
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioVivencia->dominio_id,
                'prioridad' => $prioridadDiscapacidad->dominio_id,
                'numero' => 15
            ],
            [
                'tipo' => $tipoPrioridad->dominio_id,
                'servicio' => $servicioDevoluciones->dominio_id,
                'prioridad' => $prioridadDiscapacidad->dominio_id,
                'numero' => 16
            ],
        ];

        $fichasCreadas = [];
        foreach ($fichas as $fichaData) {
            $fichasCreadas[] = Ficha::create([
                'fk_sesion_id' => $sesion->sesion_id,
                'fk_dominio_tipo_id' => $fichaData['tipo'],
                'fk_tipo_ficha_id' => $fichaData['tipo'],
                'fk_tipo_servicio_id' => $fichaData['servicio'],
                'fk_prioridad_ficha_id' => $fichaData['prioridad'],
                'numero' => $fichaData['numero'],
                'fecha_inicio' => now(),
                'fecha_registro' => now(),
                'cantidad_llamadas' => 0
            ]);
        }

        // Crear llamadas y seguimientos para las primeras 4 fichas
        for ($i = 0; $i < 4; $i++) {
            $ficha = $fichasCreadas[$i];
            $usuario = ($i % 2 === 0) ? $usuario1 : $usuario2;
            $ventanilla = ($i % 2 === 0) ? $ventanilla1 : $ventanilla2;
            $llamada = Llamada::create([
                'fk_usuario_id' => $usuario->usuario_id,
                'fk_ventanilla_id' => $ventanilla->ventanilla_id,
                'fk_ficha_id' => $ficha->ficha_id,
                'fecha' => now()
            ]);
            Seguimiento::create([
                'fk_ficha_id' => $ficha->ficha_id,
                'fk_ventanilla_id' => $ventanilla->ventanilla_id,
                'fk_usuario_id' => $usuario->usuario_id,
                'fk_dominio_estado_id' => $seguimientoEspera->dominio_id,
                'fk_dominio_accion_id' => $seguimientoEspera->dominio_id,
                'fecha' => now(),
                'observacion' => 'Ficha registrada y en espera.'
            ]);
        }

        // RolUsuario
        RolUsuario::create([
            'fk_usuario_id' => $usuario1->usuario_id,
            'fk_rol_id' => $rolOperador->rol_id
        ]);
        RolUsuario::create([
            'fk_usuario_id' => $usuario2->usuario_id,
            'fk_rol_id' => $rolOperador->rol_id
        ]);
    }
}
