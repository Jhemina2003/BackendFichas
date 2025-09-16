<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ficha extends Model
{
    use HasFactory;
    protected $table = 'fichas';
    protected $primaryKey = 'ficha_id';
    protected $guarded = [];
    public $timestamps = true;

    // Relaciones con dominios
    public function tipoFicha()
    {
        return $this->belongsTo(Dominio::class, 'fk_tipo_ficha_id', 'dominio_id');
    }

    public function tipoServicio()
    {
        return $this->belongsTo(Dominio::class, 'fk_tipo_servicio_id', 'dominio_id');
    }

    public function prioridadFicha()
    {
        return $this->belongsTo(Dominio::class, 'fk_prioridad_ficha_id', 'dominio_id');
    }
}
