<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asignacion extends Model
{
    use HasFactory;
    protected $table = 'asignaciones';
    protected $primaryKey = 'asignacion_id';
    protected $guarded = [];
    public $timestamps = true;
}
