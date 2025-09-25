<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asignacion;
use Illuminate\Support\Facades\DB;

class AsignacionSeeder extends Seeder
{
    public function run()
    {
        DB::table('asignaciones')->truncate();

        Asignacion::create([
            'fk_usuario_id' => 1,
            'fk_organizacion_id' => 1,
            'fk_sucursal_id' => 1,
            'fk_ventanilla_id' => 1,
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'activo' => true,
        ]);
        Asignacion::create([
            'fk_usuario_id' => 2,
            'fk_organizacion_id' => 1,
            'fk_sucursal_id' => 2,
            'fk_ventanilla_id' => null,
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'activo' => true,
        ]);
    }
}
