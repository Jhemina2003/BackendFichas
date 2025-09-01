<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RolUsuario extends Model
{
    use HasFactory;
    protected $table = 'rol_usuario';
    protected $guarded = [];
    public $timestamps = true;
    public $incrementing = false;
    protected $primaryKey = null;
}
