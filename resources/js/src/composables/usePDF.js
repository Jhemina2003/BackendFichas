import { pdfGenerator } from '../utils/pdfGenerator'
import { generarCodigoTurno } from '../utils/helpers'

export const usePDF = () => {
  const generarPDF = (turno) => {
    // Ya no necesitamos generar el código aquí porque viene del turno
    const ticket = {
      ...turno,
      // El código ya viene generado en turno.codigo
      fecha: new Date().toLocaleString('es-ES'),
    }

    pdfGenerator.generarTicket(ticket)
    return ticket
  }

  return {
    generarPDF,
  }
}
