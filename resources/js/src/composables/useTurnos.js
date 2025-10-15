import { ref } from 'vue'
import { useTurnosStore } from '../stores/turnos.store'

export const useTurnos = () => {
  const turnosStore = useTurnosStore()

  const llamarTurno = () => {
    turnosStore.llamarTurno()
  }

  const atenderTurno = () => {
    turnosStore.atenderTurno()
  }

  const finalizarTurno = () => {
    turnosStore.finalizarTurno()
  }

  return {
    estadoGestion: turnosStore.estadoGestion,
    turnoEnCurso: turnosStore.turnoEnCurso,
    colaTurnos: turnosStore.colaTurnos,
    estadisticas: turnosStore.estadisticas,
    llamarTurno,
    atenderTurno,
    finalizarTurno,
  }
}
