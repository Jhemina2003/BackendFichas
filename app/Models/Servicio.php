<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servicio extends Model
{
    use HasFactory;
    protected $table = 'servicios';
    protected $primaryKey = 'servicio_id';
    protected $guarded = [];
    public $timestamps = true;

    public function ventanilla()
    {
        return $this->belongsTo(Ventanilla::class, 'fk_ventanilla_id', 'ventanilla_id');
    }

    public function tipoServicio()
    {
        return $this->belongsTo(Dominio::class, 'fk_dominio_tipo_servicio_id', 'dominio_id');
    }
}
