<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventanilla;
use App\Models\Dominio;

class VentanillaTipoServicioSeeder extends Seeder
{
    public function run()
    {
        // Obtener todos los servicios disponibles
        $servicioApostilla = Dominio::where('nombre', 'apostilla')
            ->whereHas('dominioGrupo', function($q) { $q->where('nombre', 'tipo_servicio'); })
            ->first();
        $servicioLegalizaciones = Dominio::where('nombre', 'legalizaciones')
            ->whereHas('dominioGrupo', function($q) { $q->where('nombre', 'tipo_servicio'); })
            ->first();
        $servicioVivencia = Dominio::where('nombre', 'vivencia')
            ->whereHas('dominioGrupo', function($q) { $q->where('nombre', 'tipo_servicio'); })
            ->first();
        $servicioDevoluciones = Dominio::where('nombre', 'devoluciones')
            ->whereHas('dominioGrupo', function($q) { $q->where('nombre', 'tipo_servicio'); })
            ->first();

        // SUCURSAL SANTA CRUZ - Asignar servicios a todas las ventanillas
        $sucursalSantaCruz = \App\Models\Sucursal::where('nombre', 'Santa Cruz')->first();
        if ($sucursalSantaCruz) {
            $ventanillasSantaCruz = Ventanilla::where('fk_sucursal_id', $sucursalSantaCruz->sucursal_id)->get();
            
            foreach ($ventanillasSantaCruz as $ventanilla) {
                switch ($ventanilla->numero) {
                    case 1:
                        // Ventanilla 1: apostilla, legalizaciones, vivencia
                        $ventanilla->tiposServicio()->sync([
                            $servicioApostilla->dominio_id,
                            $servicioLegalizaciones->dominio_id,
                            $servicioVivencia->dominio_id
                        ]);
                        break;
                    case 2:
                        // Ventanilla 2: devoluciones
                        $ventanilla->tiposServicio()->sync([
                            $servicioDevoluciones->dominio_id
                        ]);
                        break;
                    case 3:
                        // Ventanilla 3: apostilla, legalizaciones
                        $ventanilla->tiposServicio()->sync([
                            $servicioApostilla->dominio_id,
                            $servicioLegalizaciones->dominio_id
                        ]);
                        break;
                    case 4:
                        // Ventanilla 4: vivencia, devoluciones
                        $ventanilla->tiposServicio()->sync([
                            $servicioVivencia->dominio_id,
                            $servicioDevoluciones->dominio_id
                        ]);
                        break;
                    default:
                        // Ventanillas 5-10: todos los servicios
                        $ventanilla->tiposServicio()->sync([
                            $servicioApostilla->dominio_id,
                            $servicioLegalizaciones->dominio_id,
                            $servicioVivencia->dominio_id,
                            $servicioDevoluciones->dominio_id
                        ]);
                        break;
                }
            }
        }

        // SUCURSAL LA PAZ - Asignar servicios a todas las ventanillas
        $sucursalLaPaz = \App\Models\Sucursal::where('nombre', 'La Paz')->first();
        if ($sucursalLaPaz) {
            $ventanillasLaPaz = Ventanilla::where('fk_sucursal_id', $sucursalLaPaz->sucursal_id)->get();
            
            foreach ($ventanillasLaPaz as $ventanilla) {
                switch ($ventanilla->numero) {
                    case 1:
                        // Ventanilla 1: apostilla, legalizaciones
                        $ventanilla->tiposServicio()->sync([
                            $servicioApostilla->dominio_id,
                            $servicioLegalizaciones->dominio_id
                        ]);
                        break;
                    case 2:
                        // Ventanilla 2: vivencia, devoluciones
                        $ventanilla->tiposServicio()->sync([
                            $servicioVivencia->dominio_id,
                            $servicioDevoluciones->dominio_id
                        ]);
                        break;
                    case 3:
                        // Ventanilla 3: solo devoluciones
                        $ventanilla->tiposServicio()->sync([
                            $servicioDevoluciones->dominio_id
                        ]);
                        break;
                    case 4:
                        // Ventanilla 4: apostilla, vivencia
                        $ventanilla->tiposServicio()->sync([
                            $servicioApostilla->dominio_id,
                            $servicioVivencia->dominio_id
                        ]);
                        break;
                    case 5:
                        // Ventanilla 5: todos los servicios
                        $ventanilla->tiposServicio()->sync([
                            $servicioApostilla->dominio_id,
                            $servicioLegalizaciones->dominio_id,
                            $servicioVivencia->dominio_id,
                            $servicioDevoluciones->dominio_id
                        ]);
                        break;
                }
            }
        }
    }
}
