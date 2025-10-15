import { api } from './api'

export const turnosService = {
  async obtenerColaTurnos() {
    // Simular obtención de cola de turnos
    return Promise.resolve({
      DEVOLUCION: 1,
      APOSTILLA: 2,
      LEGALIZACION: 3,
      VIVENCIA: 5,
    })
  },

  async obtenerEstadisticas() {
    // Simular obtención de estadísticas
    return Promise.resolve({
      finalizados: 3,
      ausentados: 0,
      total: 3,
    })
  },
}
