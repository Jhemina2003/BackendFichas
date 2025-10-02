<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $hidden = ['password'];
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

    public function roles()
    {
        return $this->belongsToMany(
            \App\Models\Rol::class,
            'rol_usuario',
            'fk_usuario_id',
            'fk_rol_id'
        );
    }
}
