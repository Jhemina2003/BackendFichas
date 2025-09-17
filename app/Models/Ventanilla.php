<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ventanilla extends Model
{
    use HasFactory;
    protected $table = 'ventanillas';
    protected $primaryKey = 'ventanilla_id';
    protected $guarded = [];
    public $timestamps = true;

    // Estados posibles: abierta, cerrada
    const ESTADO_ABIERTA = 'abierta';
    const ESTADO_CERRADA = 'cerrada';
}
