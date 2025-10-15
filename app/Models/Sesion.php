<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sesion extends Model
{
    use HasFactory;
    protected $table = 'sesiones';
    protected $primaryKey = 'sesion_id';
    protected $guarded = [];
    public $timestamps = true;

    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'fk_sucursal_id', 'sucursal_id');
    }

    public function organizacion()
    {
        // Relación a través de sucursal
        return $this->sucursal ? $this->sucursal->organizacion() : null;
    }

    // Relación con dominio de estado (corregido: debe apuntar a fk_dominio_estado_id)
    public function estadoDominio()
    {
        return $this->belongsTo(\App\Models\Dominio::class, 'fk_dominio_estado_id', 'dominio_id');
    }
}
