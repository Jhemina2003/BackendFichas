<template>
  <div class="generar-ticket-actions">
    <div v-if="paso === 'servicio'" class="servicios-grid">
      <button
        v-for="servicio in servicios"
        :key="servicio.key"
        @click="onSeleccionServicio(servicio.key)"
        class="servicio-btn"
      >
        <span class="servicio-icon" aria-hidden="true">
          <!-- emojis representando cada servicio -->
          <template v-if="servicio.key === SERVICIOS.APOSTILLA">🏷️</template>
          <template v-else-if="servicio.key === SERVICIOS.LEGALIZACION">📜</template>
          <template v-else-if="servicio.key === SERVICIOS.DEVOLUCION">🔄</template>
          <template v-else-if="servicio.key === SERVICIOS.VIVENCIA">🏠</template>
        </span>
        <span class="servicio-nombre">{{ servicio.nombre }}</span>
      </button>
    </div>

    <div v-else-if="paso === 'tipo'" class="tipos-grid">
      <button
        v-for="tipo in tipos"
        :key="tipo.key"
        @click="onSeleccionTipo(tipo.key)"
        class="tipo-btn"
      >
        <div class="tipo-inner">
          <template v-if="tipo.key === 'NORMAL'">
            <!-- persona (NORMAL) -->
            <span class="tipo-icon" aria-hidden="true">&#128100;</span>
          </template>
          <template v-else>
            <!-- PREFERENCIAL: mujer embarazada, silla de ruedas, madre con bebé, bastón -->
            <span class="tipo-icons-line" aria-hidden="true">
              &#129328; &#128118; &#9855; &#128116;
            </span>
          </template>
          <span class="tipo-nombre">{{ tipo.nombre }}</span>
        </div>
      </button>
    </div>

    <div v-else-if="paso === 'descarga'" class="descarga-wrap">
      <DescargaTicket :ticket="ticketGenerado" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useTurnosStore } from '../../../stores/turnos.store'
import { usePDF } from '../../../composables/usePDF'
import DescargaTicket from './DescargaTicket.vue'
import { SERVICIOS } from '../../../utils/constants'
import { formatearServicio } from '../../../utils/helpers'
// icons replaced by emojis
// icons replaced by emojis

const turnosStore = useTurnosStore()
const { generarPDF } = usePDF()

const paso = ref('servicio')
const servicioSeleccionado = ref('')
const tipoSeleccionado = ref('')
const ticketGenerado = ref(null)

const servicios = [
  { key: SERVICIOS.APOSTILLA, nombre: formatearServicio(SERVICIOS.APOSTILLA) },
  { key: SERVICIOS.LEGALIZACION, nombre: formatearServicio(SERVICIOS.LEGALIZACION) },
  { key: SERVICIOS.DEVOLUCION, nombre: formatearServicio(SERVICIOS.DEVOLUCION) },
  { key: SERVICIOS.VIVENCIA, nombre: formatearServicio(SERVICIOS.VIVENCIA) },
]

// icons replaced by emojis; function iconFor removed

const tipos = [
  { key: 'NORMAL', nombre: 'NORMAL' },
  { key: 'PREFERENCIAL', nombre: 'PREFERENCIAL' },
]

const onSeleccionServicio = (servicio) => {
  servicioSeleccionado.value = servicio
  paso.value = 'tipo'
}

const onSeleccionTipo = (tipo) => {
  tipoSeleccionado.value = tipo
  // generar turno
  const nuevoTurno = turnosStore.generarNuevoTurno(servicioSeleccionado.value, tipo)
  ticketGenerado.value = generarPDF(nuevoTurno)
  paso.value = 'descarga'
  setTimeout(() => {
    resetear()
  }, 1500)
}

const resetear = () => {
  paso.value = 'servicio'
  servicioSeleccionado.value = ''
  tipoSeleccionado.value = ''
  ticketGenerado.value = null
}
</script>

<style scoped>
.generar-ticket-actions {
  width: 100%;
  /* allow this component to grow inside the parent flex container */
  flex: 1;
  display: flex;
  align-items: stretch;
  justify-content: center;
  min-height: 0; /* important so children can use height:100% inside flex parents */
}

.servicios-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  width: 100%;
  height: 100%;
  min-height: 0;
}

.servicio-btn {
  /* vivid blue for customers */
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  border: 2px solid rgba(0, 123, 255, 0.15);
  padding: var(--spacing-xl);
  text-align: center;
  cursor: pointer;
  transition:
    transform 0.18s ease,
    box-shadow 0.18s ease,
    filter 0.18s ease;
  font-size: var(--font-size-lg);
  font-weight: 600;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  min-height: 0;
  box-shadow: 0 6px 18px rgba(51, 153, 255, 0.12);
}

.servicio-btn:hover {
  transform: translateY(-3px);
  filter: brightness(0.95);
  box-shadow: 0 10px 24px rgba(51, 153, 255, 0.18);
}

.tipos-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  width: 100%;
  height: 100%;
  min-height: 0;
}

.tipo-btn {
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  border: none;
  font-size: 5.6rem; /* doubled */
  font-weight: 700;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  min-height: 0;
  box-shadow: 0 6px 18px rgba(51, 153, 255, 0.12);
  transition:
    transform 0.18s ease,
    filter 0.18s ease,
    box-shadow 0.18s ease;
}

.tipo-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
}

.tipo-icon {
  font-size: 6.4rem; /* doubled */
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.tipo-icons-line {
  display: inline-flex;
  gap: 12px;
  align-items: center;
  font-size: 6.8rem; /* doubled */
}

.tipo-nombre {
  font-size: 4.8rem; /* doubled */
  font-weight: 800;
}

.servicio-icon {
  font-size: 3.6rem; /* large icon */
  margin-right: 12px;
  display: inline-flex;
  align-items: center;
}

.servicio-nombre {
  font-size: 2.6rem;
  font-weight: 700;
  line-height: 1.05;
}

.tipo-btn:hover {
  transform: translateY(-3px);
  filter: brightness(0.95);
  box-shadow: 0 10px 24px rgba(51, 153, 255, 0.18);
}

.descarga-wrap {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
