<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        // Si la tabla ya tiene servicios, no duplicar
        if (DB::table('servicios')->count() === 0) {
            $servicios = [
                ['nombre' => 'apostilla'],
                ['nombre' => 'legalizaciones'],
                ['nombre' => 'vivencia'],
                ['nombre' => 'devoluciones'],
            ];
            foreach ($servicios as $servicio) {
                DB::table('servicios')->insert([
                    'nombre' => $servicio['nombre'],
                    'fk_ventanilla_id' => 1, // Ajusta según tu lógica
                    'fk_dominio_tipo_servicio_id' => 1, // Ajusta según tu lógica
                    'fecha' => Carbon::now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
