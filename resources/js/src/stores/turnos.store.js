import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { generarCodigoTurno } from '../utils/helpers'

export const useTurnosStore = defineStore('turnos', () => {
  // Estado del funcionario
  const estadoGestion = ref('inicial') // 'inicial', 'llamando', 'atendiendo'
  const turnoEnCurso = ref(null)

  // Cola de turnos como array de objetos (inicia vacía)
  const colaTurnos = ref([])

  // Estadísticas (inician en cero)
  const estadisticas = ref({
    finalizados: 0,
    ausentados: 0,
    total: 0,
  })

  // Turnos llamados para pantalla pública
  const turnosLlamados = ref([])

  const totalTurnos = computed(() => colaTurnos.value.length)

  const llamarTurno = () => {
    if (colaTurnos.value.length === 0) {
      alert('No hay turnos en espera')
      return
    }

    estadoGestion.value = 'llamando'
    // Tomar el primer turno de la cola
    const turno = colaTurnos.value[0]
    turnoEnCurso.value = turno

    // Eliminar el turno llamado de la cola
    colaTurnos.value = colaTurnos.value.filter((t) => t.id !== turno.id)

    // Eliminar el turno llamado de eventosTurnos en localStorage
    const eventos = JSON.parse(localStorage.getItem('eventosTurnos') || '[]')
    const eventosFiltrados = eventos.filter((e) => !(e.data && e.data.id === turno.id))
    localStorage.setItem('eventosTurnos', JSON.stringify(eventosFiltrados))
    // Disparar evento de storage para sincronización
    window.dispatchEvent(
      new StorageEvent('storage', {
        key: 'eventosTurnos',
        newValue: JSON.stringify(eventosFiltrados),
      }),
    )

    // Agregar a turnos llamados
    const turnoLlamado = { ...turno, ventanilla: 1 }
    turnosLlamados.value.unshift(turnoLlamado)

    // NUEVO: Emitir evento de turno llamado para pantalla pública
    emitirEventoTurnoLlamado(turnoLlamado)
  }

  const atenderTurno = () => {
    estadoGestion.value = 'atendiendo'
    localStorage.setItem('parpadeoTicketLlamado', 'false')
    window.dispatchEvent(
      new StorageEvent('storage', { key: 'parpadeoTicketLlamado', newValue: 'false' }),
    )
  }

  const finalizarTurno = () => {
    // Remover el turno actual de la cola
    if (turnoEnCurso.value) {
      colaTurnos.value = colaTurnos.value.filter((t) => t.id !== turnoEnCurso.value.id)
    }

    estadisticas.value.finalizados++
    estadisticas.value.total++
    turnoEnCurso.value = null
    estadoGestion.value = 'inicial'
    localStorage.setItem('parpadeoTicketLlamado', 'false')
    window.dispatchEvent(
      new StorageEvent('storage', { key: 'parpadeoTicketLlamado', newValue: 'false' }),
    )
  }

  const marcarAusencia = () => {
    // Remover el turno actual de la cola
    if (turnoEnCurso.value) {
      colaTurnos.value = colaTurnos.value.filter((t) => t.id !== turnoEnCurso.value.id)
    }

    estadisticas.value.ausentados++
    estadisticas.value.total++
    turnoEnCurso.value = null
    estadoGestion.value = 'inicial'
    localStorage.setItem('parpadeoTicketLlamado', 'false')
    window.dispatchEvent(
      new StorageEvent('storage', { key: 'parpadeoTicketLlamado', newValue: 'false' }),
    )
  }

  const rellamarTurno = () => {
    if (turnoEnCurso.value) {
      // Emitir evento de turno llamado para pantalla pública (igual que llamarTurno)
      const turnoLlamado = { ...turnoEnCurso.value, ventanilla: 1 }
      emitirEventoTurnoLlamado(turnoLlamado)
      // Activar parpadeo
      localStorage.setItem('parpadeoTicketLlamado', 'true')
      window.dispatchEvent(
        new StorageEvent('storage', { key: 'parpadeoTicketLlamado', newValue: 'true' }),
      )
    }
  }

  const agregarObservacion = (observacion) => {
    if (turnoEnCurso.value) {
      turnoEnCurso.value.observacion = observacion
    }
  }

  const redirigirTurno = (motivo) => {
    if (turnoEnCurso.value) {
      turnoEnCurso.value.redirigido = true
      turnoEnCurso.value.motivoRedireccion = motivo
    }
    finalizarTurno()
  }

  // Obtener próximo número para un servicio
  const obtenerProximoNumero = (servicio, tipo) => {
    const turnosDelServicioYTipo = colaTurnos.value.filter(
      (t) => t.servicio === servicio && t.tipo === tipo,
    )

    if (turnosDelServicioYTipo.length === 0) {
      return 1
    }

    return Math.max(...turnosDelServicioYTipo.map((t) => t.numero)) + 1
  }

  // Generar turno sin dependencia de socketStore
  const generarNuevoTurno = (servicio, tipo) => {
    const numero = obtenerProximoNumero(servicio, tipo)
    const codigo = generarCodigoTurno(servicio, numero, tipo)

    const nuevoTurno = {
      id: Date.now() + Math.random(),
      servicio,
      tipo,
      numero,
      codigo: codigo,
      timestamp: new Date(),
      posicion: colaTurnos.value.length + 1,
    }

    // Agregar a la cola
    colaTurnos.value.push(nuevoTurno)

    // Emitir evento usando localStorage directamente
    emitirEventoNuevoTurno(nuevoTurno)

    return nuevoTurno
  }

  // Función para emitir eventos sin dependencia de store
  const emitirEventoNuevoTurno = (turno) => {
    console.log('Emitting nuevo turno:', turno)
    const eventosActuales = JSON.parse(localStorage.getItem('eventosTurnos') || '[]')
    eventosActuales.push({
      type: 'NUEVO_TURNO',
      data: turno,
      timestamp: new Date().getTime(),
    })
    localStorage.setItem('eventosTurnos', JSON.stringify(eventosActuales))

    // Disparar evento de storage para sincronización
    window.dispatchEvent(
      new StorageEvent('storage', {
        key: 'eventosTurnos',
        newValue: JSON.stringify(eventosActuales),
      }),
    )
  }

  // NUEVO: Función para emitir evento de turno llamado
  const emitirEventoTurnoLlamado = (turnoLlamado) => {
    console.log('Emitting turno llamado:', turnoLlamado)
    const eventosActuales = JSON.parse(localStorage.getItem('eventosTurnosLlamados') || '[]')
    eventosActuales.unshift({
      type: 'TURNO_LLAMADO',
      data: turnoLlamado,
      timestamp: new Date().getTime(),
    })

    // Mantener solo los últimos 10 eventos para no llenar localStorage
    const eventosLimitados = eventosActuales.slice(0, 10)
    localStorage.setItem('eventosTurnosLlamados', JSON.stringify(eventosLimitados))

    // Disparar evento de storage para sincronización
    window.dispatchEvent(
      new StorageEvent('storage', {
        key: 'eventosTurnosLlamados',
        newValue: JSON.stringify(eventosLimitados),
      }),
    )
  }

  // Sincronizar cola desde eventos sin dependencia de store
  const sincronizarColaDesdeEventos = () => {
    const eventos = JSON.parse(localStorage.getItem('eventosTurnos') || '[]')

    // Obtener todos los turnos de eventos
    const turnosDeEventos = eventos
      .filter((evento) => evento.type === 'NUEVO_TURNO')
      .map((evento) => evento.data)

    // Combinar con cola actual (evitando duplicados)
    const idsExistentes = new Set(colaTurnos.value.map((t) => t.id))
    const nuevosTurnos = turnosDeEventos.filter((t) => !idsExistentes.has(t.id))

    if (nuevosTurnos.length > 0) {
      colaTurnos.value.push(...nuevosTurnos)
      // Ordenar por timestamp
      colaTurnos.value.sort((a, b) => a.timestamp - b.timestamp)
    }
  }

  // NUEVO: Sincronizar turnos llamados desde eventos
  const sincronizarTurnosLlamadosDesdeEventos = () => {
    const eventos = JSON.parse(localStorage.getItem('eventosTurnosLlamados') || '[]')

    // Obtener todos los turnos llamados de eventos
    const turnosLlamadosDeEventos = eventos
      .filter((evento) => evento.type === 'TURNO_LLAMADO')
      .map((evento) => evento.data)

    // Combinar con turnos llamados actuales (evitando duplicados)
    const idsExistentes = new Set(turnosLlamados.value.map((t) => t.id))
    const nuevosTurnosLlamados = turnosLlamadosDeEventos.filter((t) => !idsExistentes.has(t.id))

    if (nuevosTurnosLlamados.length > 0) {
      turnosLlamados.value.unshift(...nuevosTurnosLlamados)
      // Mantener solo los últimos 10 turnos llamados
      turnosLlamados.value = turnosLlamados.value.slice(0, 10)
    }
  }

  return {
    estadoGestion,
    turnoEnCurso,
    colaTurnos,
    estadisticas,
    turnosLlamados,
    totalTurnos,
    llamarTurno,
    atenderTurno,
    finalizarTurno,
    marcarAusencia,
    rellamarTurno,
    agregarObservacion,
    redirigirTurno,
    generarNuevoTurno,
    obtenerProximoNumero,
    sincronizarColaDesdeEventos,
    sincronizarTurnosLlamadosDesdeEventos, // NUEVO
  }
})
