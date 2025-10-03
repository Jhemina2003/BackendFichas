<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicioNombreSeeder extends Seeder
{
    public function run(): void
    {
        // Poblar servicios con nombres explícitos
        $nombres = ['apostilla', 'legalizaciones', 'vivencia', 'devoluciones'];
        $servicios = DB::table('servicios')->get();
        $i = 0;
        foreach ($servicios as $servicio) {
            $nombre = $nombres[$i % count($nombres)];
            DB::table('servicios')->where('servicio_id', $servicio->servicio_id)->update(['nombre' => $nombre]);
            $i++;
        }
    }
}
