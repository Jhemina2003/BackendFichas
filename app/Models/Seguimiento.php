<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seguimiento extends Model
{
    use HasFactory;
    protected $table = 'seguimientos';
    protected $primaryKey = 'seguimiento_id';
    protected $guarded = [];
    public $timestamps = true;

    public function dominioEstado()
    {
        return $this->belongsTo(\App\Models\Dominio::class, 'fk_dominio_estado_id', 'dominio_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'fk_usuario_id', 'usuario_id');
    }

    public function ventanilla()
    {
        return $this->belongsTo(\App\Models\Ventanilla::class, 'fk_ventanilla_id', 'ventanilla_id');
    }
}
