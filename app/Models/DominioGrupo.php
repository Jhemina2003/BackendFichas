<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DominioGrupo extends Model
{
    use HasFactory;
    protected $table = 'dominios_grupo';
    protected $primaryKey = 'dominio_grupo_id';
    protected $guarded = [];
    public $timestamps = true;
}
