<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventanilla;
use App\Models\Dominio;

class VentanillaTipoServicioSeeder extends Seeder
{
    public function run()
    {
        // Ventanilla 1: apostilla, legalizaciones, viviencia
        $ventanilla1 = Ventanilla::where('numero', 1)->first();
        $servicios1 = Dominio::whereIn('nombre', ['apostilla', 'legalizaciones', 'viviencia'])
            ->whereHas('dominioGrupo', function($q) { $q->where('nombre', 'tipo_servicio'); })
            ->pluck('dominio_id');
        if ($ventanilla1) {
            $ventanilla1->tiposServicio()->sync($servicios1);
        }

        // Ventanilla 2: devoluciones
        $ventanilla2 = Ventanilla::where('numero', 2)->first();
        $servicios2 = Dominio::whereIn('nombre', ['devoluciones'])
            ->whereHas('dominioGrupo', function($q) { $q->where('nombre', 'tipo_servicio'); })
            ->pluck('dominio_id');
        if ($ventanilla2) {
            $ventanilla2->tiposServicio()->sync($servicios2);
        }

        // Puedes agregar más ventanillas y servicios según lo necesites
    }
}
