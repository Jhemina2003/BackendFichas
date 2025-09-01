<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bloqueo extends Model
{
    use HasFactory;
    protected $table = 'bloqueos';
    protected $primaryKey = 'bloqueo_id';
    protected $guarded = [];
    public $timestamps = true;
}
