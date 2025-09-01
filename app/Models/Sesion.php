<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sesion extends Model
{
    use HasFactory;
    protected $table = 'sesiones';
    protected $primaryKey = 'sesion_id';
    protected $guarded = [];
    public $timestamps = true;
}
