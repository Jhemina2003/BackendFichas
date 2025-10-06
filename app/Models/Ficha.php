<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ficha extends Model
{

    use HasFactory;
    protected $table = 'fichas';
    protected $primaryKey = 'ficha_id';
    protected $guarded = [];
    public $timestamps = true;
    protected $appends = ['numero_formateado', 'estado_actual'];

    public function getEstadoActualAttribute()
    {
        $ultimoSeguimiento = $this->seguimientos()->latest('created_at')->first();
        if ($ultimoSeguimiento && $ultimoSeguimiento->dominioEstado) {
            return $ultimoSeguimiento->dominioEstado->nombre;
        }
        return null;
    }

    public function seguimientos()
    {
        return $this->hasMany(\App\Models\Seguimiento::class, 'fk_ficha_id', 'ficha_id');
    }

    public function sesion()
    {
        return $this->belongsTo(\App\Models\Sesion::class, 'fk_sesion_id', 'sesion_id');
    }


    // Relación con tipo de ficha
    public function tipoFicha()
    {
        return $this->belongsTo(Dominio::class, 'fk_tipo_ficha_id', 'dominio_id');
    }

    // Relación con tipo de servicio
    public function tipoServicio()
    {
        return $this->belongsTo(Dominio::class, 'fk_tipo_servicio_id', 'dominio_id');
    }


    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'fk_usuario_id', 'usuario_id');
    }

    public function getNumeroFormateadoAttribute()
    {
        $tipoServicio = $this->tipoServicio;
        $tipoFicha = $this->tipoFicha;
        $nombreServicio = $tipoServicio ? strtoupper($tipoServicio->nombre) : 'FICHA';
        $prefijo = substr($nombreServicio, 0, 4);
        $esPreferencial = $this->tipoFicha && $this->tipoFicha->nombre === 'preferencial';
        $prefijoFinal = $esPreferencial ? 'P.' . $prefijo : $prefijo;
        return $prefijoFinal . '.' . $this->numero;
    }
}
