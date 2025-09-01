<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organizacion extends Model
{
    use HasFactory;
    protected $table = 'organizaciones';
    protected $primaryKey = 'organizacion_id';
    protected $guarded = [];
    public $timestamps = true;
}
