<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organizacion extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'organizaciones';
    protected $primaryKey = 'organizacion_id';
    protected $guarded = [];
    protected $fillable = [
        'nombre',
        'fk_cod_contacto',
        // ...existing fillable fields...
    ];
    public $timestamps = true;
}
