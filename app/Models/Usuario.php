<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Model
{
    use HasFactory;
    protected $table = 'usuarios';
    protected $primaryKey = 'usuario_id';
    protected $guarded = [];
    public $timestamps = true;
    protected $softDelete = true;

    public function ventanilla()
    {
        return $this->belongsTo(\App\Models\Ventanilla::class, 'fk_ventanilla_id', 'ventanilla_id');
    }
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'fk_sucursal_id', 'sucursal_id');
    }
}
