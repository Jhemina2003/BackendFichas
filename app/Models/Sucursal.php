<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'sucursales';
    protected $primaryKey = 'sucursal_id';
    protected $guarded = [];
    public $timestamps = true;

    public function organizacion()
    {
        return $this->belongsTo(\App\Models\Organizacion::class, 'fk_organizacion_id', 'organizacion_id');
    }
}
