export const SERVICIOS = {
  APOSTILLA: 'APOSTILLA',
  LEGALIZACION: 'LEGALIZACION',
  DEVOLUCION: 'DEVOLUCION',
  VIVENCIA: 'VIVENCIA',
}

export const TIPOS_TURNO = {
  NORMAL: 'NORMAL',
  PREFERENCIAL: 'PREFERENCIAL',
}

export const ESTADOS_GESTION = {
  INICIAL: 'inicial',
  LLAMANDO: 'llamando',
  ATENDIENDO: 'atendiendo',
}

export const OBSERVACIONES_PREDEFINIDAS = [
  'Documentación incompleta',
  'Requiere información adicional',
  'Pago pendiente',
  'Turno duplicado',
  'Otro',
]

// NUEVO: Prefijos para los servicios
export const PREFIJOS_SERVICIOS = {
  [SERVICIOS.APOSTILLA]: 'APOS',
  [SERVICIOS.LEGALIZACION]: 'LEGA',
  [SERVICIOS.DEVOLUCION]: 'DEVO',
  [SERVICIOS.VIVENCIA]: 'VIVE',
}
