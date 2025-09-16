<?php

namespace App\Enums;

enum TipoServicioEnum: string
{
    case APOSTILLA = 'apostilla';
    case LEGALIZACIONES = 'legalizaciones';
    case VIVENCIA = 'vivencia';
    case DEVOLUCIONES = 'devoluciones';
}
