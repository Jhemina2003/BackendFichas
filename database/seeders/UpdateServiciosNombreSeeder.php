<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateServiciosNombreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Puedes ajustar estos nombres según tus servicios reales
        $servicios = [
            1 => 'apostilla',
            2 => 'legalizaciones',
            3 => 'vivencia',
            4 => 'devoluciones',
        ];

        foreach ($servicios as $id => $nombre) {
            DB::table('servicios')->where('servicio_id', $id)->update(['nombre' => $nombre]);
        }
    }
}
