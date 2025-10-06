<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentanillaTipoServicio extends Model
{
    protected $table = 'ventanilla_tipo_servicio';
    protected $fillable = ['ventanilla_id', 'dominio_id'];
    public $timestamps = true;

    public function ventanilla()
    {
        return $this->belongsTo(Ventanilla::class, 'ventanilla_id', 'ventanilla_id');
    }

    public function tipoServicio()
    {
        return $this->belongsTo(Dominio::class, 'dominio_id', 'dominio_id');
    }
}
