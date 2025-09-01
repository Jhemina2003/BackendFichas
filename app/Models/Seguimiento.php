<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seguimiento extends Model
{
    use HasFactory;
    protected $table = 'seguimientos';
    protected $primaryKey = 'seguimiento_id';
    protected $guarded = [];
    public $timestamps = true;
}
