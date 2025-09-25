<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asignacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asignaciones';
    protected $primaryKey = 'asignacion_id';
    public $timestamps = false;

    protected $fillable = [
        'fk_usuario_id',
        'fk_organizacion_id',
        'fk_sucursal_id',
        'fk_ventanilla_id',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'fk_usuario_id', 'usuario_id');
    }

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'fk_organizacion_id', 'organizacion_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'fk_sucursal_id', 'sucursal_id');
    }

    public function ventanilla()
    {
        return $this->belongsTo(Ventanilla::class, 'fk_ventanilla_id', 'ventanilla_id');
    }
}
