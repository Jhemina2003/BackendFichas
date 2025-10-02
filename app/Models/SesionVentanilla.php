<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SesionVentanilla extends Model
{
    use HasFactory;
    protected $table = 'sesiones_ventanilla';
    protected $primaryKey = 'sesion_ventanilla_id';
    protected $guarded = [];
    public $timestamps = true;

    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'fk_sesion_id', 'sesion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'fk_usuario_id', 'usuario_id');
    }

    public function ventanilla()
    {
        return $this->belongsTo(Ventanilla::class, 'fk_ventanilla_id', 'ventanilla_id');
    }
}
