<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Llamada extends Model
{
    use HasFactory;
    protected $table = 'llamadas';
    protected $primaryKey = 'llamada_id';
    protected $guarded = [];
    public $timestamps = true;
}
