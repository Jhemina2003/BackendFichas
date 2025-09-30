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
        $fichas = [
            // Normales
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','tipo_servicio'=>'apostilla','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','tipo_servicio'=>'legalizaciones','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','tipo_servicio'=>'devoluciones','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','tipo_servicio'=>'vivencia','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            // Preferenciales
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'preferencial','tipo_servicio'=>'apostilla','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'preferencial','tipo_servicio'=>'legalizaciones','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'preferencial','tipo_servicio'=>'devoluciones','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'preferencial','tipo_servicio'=>'vivencia','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            // Para ver el incremento
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','tipo_servicio'=>'apostilla','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'preferencial','tipo_servicio'=>'apostilla','fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
        ];
        foreach ($fichas as $data) {
            $service->crearFicha($data);
        }
    }
}
