<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ventanilla extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'ventanillas';
    protected $primaryKey = 'ventanilla_id';
    protected $guarded = [];
    public $timestamps = true;

    // Estados posibles: abierta, cerrada
    const ESTADO_ABIERTA = 'abierta';
    const ESTADO_CERRADA = 'cerrada';

    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'fk_sucursal_id', 'sucursal_id');
    }

    public function tiposServicio()
    {
        return $this->belongsToMany(
            Dominio::class,
            'ventanilla_tipo_servicio',
            'ventanilla_id',
            'dominio_id'
        );
    }

    // Relación con usuario asignado a la ventanilla
    public function usuario()
    {
        return $this->hasOne(\App\Models\Usuario::class, 'fk_ventanilla_id', 'ventanilla_id');
    }
}
