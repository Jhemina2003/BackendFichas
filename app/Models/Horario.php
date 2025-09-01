<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Horario extends Model
{
    use HasFactory;
    protected $table = 'horarios';
    protected $primaryKey = 'horario_id';
    protected $guarded = [];
    public $timestamps = true;
}
