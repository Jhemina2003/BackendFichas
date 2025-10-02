<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicioVentanilla extends Model
{
    use HasFactory;
    protected $table = 'servicio_ventanilla';
    protected $primaryKey = 'servicio_ventanilla_id';
    protected $guarded = [];
    public $timestamps = true;

    public function ventanilla()
    {
        return $this->belongsTo(Ventanilla::class, 'fk_ventanilla_id', 'ventanilla_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'fk_servicio_id', 'servicio_id');
    }
}
