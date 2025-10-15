<template>
  <div class="generar-ticket">
    <div class="ticket-container">
      <SeleccionServicio
        v-if="pasoActual === 'servicio'"
        @servicio-seleccionado="seleccionarServicio"
      />

      <SeleccionTipo v-else-if="pasoActual === 'tipo'" @tipo-seleccionado="seleccionarTipo" />

      <DescargaTicket v-else-if="pasoActual === 'descarga'" :ticket="ticketGenerado" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useTurnosStore } from '../../../stores/turnos.store'
import { usePDF } from '../../../composables/usePDF'
import SeleccionServicio from './SeleccionServicio.vue'
import SeleccionTipo from './SeleccionTipo.vue'
import DescargaTicket from './DescargaTicket.vue'

const turnosStore = useTurnosStore()
const { generarPDF } = usePDF()

const pasoActual = ref('servicio')
const servicioSeleccionado = ref('')
const tipoSeleccionado = ref('')
const ticketGenerado = ref(null)

const seleccionarServicio = (servicio) => {
  console.log('Servicio seleccionado:', servicio)
  servicioSeleccionado.value = servicio
  pasoActual.value = 'tipo'
}

const seleccionarTipo = (tipo) => {
  console.log('Tipo seleccionado:', tipo)
  tipoSeleccionado.value = tipo

  // Generar el turno y el PDF inmediatamente
  const nuevoTurno = turnosStore.generarNuevoTurno(servicioSeleccionado.value, tipo)
  console.log('Nuevo turno generado:', nuevoTurno)
  ticketGenerado.value = generarPDF(nuevoTurno)

  // Mostrar pantalla de descarga por un momento
  pasoActual.value = 'descarga'

  // Volver automáticamente a servicios después de 1.5 segundos
  setTimeout(() => {
    resetearFlujo()
  }, 1500)
}

const resetearFlujo = () => {
  pasoActual.value = 'servicio'
  servicioSeleccionado.value = ''
  tipoSeleccionado.value = ''
  ticketGenerado.value = null
  console.log('Flujo resetado, volviendo a servicios')
}
</script>

<style scoped>
.generar-ticket {
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: stretch;
}

.ticket-container {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: stretch;
}
</style>
