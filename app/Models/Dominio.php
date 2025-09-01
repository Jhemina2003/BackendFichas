<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dominio extends Model
{
    use HasFactory;
    protected $table = 'dominios';
    protected $primaryKey = 'dominio_id';
    protected $guarded = [];
    public $timestamps = true;
}
