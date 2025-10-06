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
        
        // Obtener tipos de servicio desde dominios_grupo tipo_servicio
        $tiposServicio = Dominio::whereHas('dominioGrupo', function($q) {
            $q->where('nombre', 'tipo_servicio');
        })->get()->keyBy('nombre');

        $esperados = ['apostilla', 'legalizaciones', 'viviencia', 'devoluciones'];
        foreach ($esperados as $nombre) {
            if (!isset($tiposServicio[$nombre])) {
                throw new \Exception("Falta el tipo de servicio '$nombre' en la tabla dominios");
            }
        }

        $fichas = [
            // Normales
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','fk_tipo_servicio_id'=>$tiposServicio['apostilla']->dominio_id,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','fk_tipo_servicio_id'=>$tiposServicio['legalizaciones']->dominio_id,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','fk_tipo_servicio_id'=>$tiposServicio['viviencia']->dominio_id,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'normal','fk_tipo_servicio_id'=>$tiposServicio['devoluciones']->dominio_id,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
            // Preferenciales
            ['fk_sesion_id'=>$sesion->sesion_id,'tipo_ficha'=>'preferencial','fk_tipo_servicio_id'=>$tiposServicio['apostilla']->dominio_id,'fecha_inicio'=>$fecha,'fecha_registro'=>$fecha,'cantidad_llamadas'=>0],
        ];
        foreach ($fichas as $data) {
            $service->crearFicha($data);
        }
    }
}
