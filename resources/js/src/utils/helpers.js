import { SERVICIOS, TIPOS_TURNO, PREFIJOS_SERVICIOS } from './constants'

export const formatearServicio = (servicio) => {
  const nombres = {
    [SERVICIOS.APOSTILLA]: 'APOSTILLA',
    [SERVICIOS.LEGALIZACION]: 'LEGALIZACIÓN DE DOCUMENTOS',
    [SERVICIOS.DEVOLUCION]: 'DEVOLUCIÓN DE DOCUMENTOS',
    [SERVICIOS.VIVENCIA]: 'VIVENCIA',
  }
  return nombres[servicio] || servicio
}

// ACTUALIZADO: Generar código con prefijo P. para preferencial
export const generarCodigoTurno = (servicio, numero, tipo = TIPOS_TURNO.NORMAL) => {
  const prefijo = PREFIJOS_SERVICIOS[servicio]

  if (!prefijo) {
    return `TKT.${numero}`
  }

  if (tipo === TIPOS_TURNO.PREFERENCIAL) {
    return `P.${prefijo}.${numero}`
  }

  return `${prefijo}.${numero}`
}

export const formatearFecha = (fecha = new Date()) => {
  return fecha.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

export const formatearHora = (fecha = new Date()) => {
  return fecha.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}
