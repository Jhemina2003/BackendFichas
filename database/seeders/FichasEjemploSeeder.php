<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\FichaService;
use App\Models\Dominio;
use App\Models\Sesion;

class FichasEjemploSeeder extends Seeder
{
    public function run()
    {
        $fecha = now();
        $sesion = Sesion::firstOrCreate(['sesion_id' => 1]);
        $service = new FichaService();
        // Debe actualizarse para usar fk_servicio_id en vez de tipo_servicio string
        // Ejemplo de obtención de servicios:
        $servicios = \App\Models\Servicio::all()->keyBy('nombre');
        $fichas = [
            // Normales
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','fk_servicio_id'=>$servicios['apostilla']->servicio_id ?? 1,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','fk_servicio_id'=>$servicios['legalizaciones']->servicio_id ?? 2,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            // ...agregar más según servicios disponibles
        ];
        foreach ($fichas as $data) {
            $service->crearFicha($data);
        }
    }
}
