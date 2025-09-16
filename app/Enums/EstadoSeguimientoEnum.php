<?php

namespace App\Enums;

enum EstadoSeguimientoEnum: string
{
    case EN_ESPERA = 'en_espera';
    case LLAMADO = 'llamado';
    case ATENDIDO = 'atendido';
    case FINALIZADO = 'finalizado';
    case CANCELADO = 'cancelado';
}
