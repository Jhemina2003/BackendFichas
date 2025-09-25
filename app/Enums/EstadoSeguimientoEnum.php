<?php

namespace App\Enums;

enum EstadoSeguimientoEnum: string
{
    case EN_ESPERA = 'en_espera';
    case LLAMADO = 'llamado';
    case EN_ATENCION = 'en_atencion';
    case FINALIZADO = 'finalizado';
    case CANCELADO = 'cancelado';
    case AUSENTE = 'ausente';
    case REASIGNADO = 'reasignado';
}
