<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ficha extends Model
{
    use HasFactory;
    protected $table = 'fichas';
    protected $primaryKey = 'ficha_id';
    protected $guarded = [];
    public $timestamps = true;
}
