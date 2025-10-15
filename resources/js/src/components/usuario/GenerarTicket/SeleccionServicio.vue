<template>
  <div class="seleccion-servicio">
    <div class="servicios-grid">
      <button
        v-for="servicio in servicios"
        :key="servicio.key"
        @click="seleccionarServicio(servicio.key)"
        class="servicio-btn"
      >
        <span class="servicio-nombre">{{ servicio.nombre }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { SERVICIOS } from '../../../utils/constants'
import { formatearServicio } from '../../../utils/helpers'

const emit = defineEmits(['servicio-seleccionado'])

const servicios = [
  { key: SERVICIOS.APOSTILLA, nombre: formatearServicio(SERVICIOS.APOSTILLA) },
  { key: SERVICIOS.LEGALIZACION, nombre: formatearServicio(SERVICIOS.LEGALIZACION) },
  { key: SERVICIOS.DEVOLUCION, nombre: formatearServicio(SERVICIOS.DEVOLUCION) },
  { key: SERVICIOS.VIVENCIA, nombre: formatearServicio(SERVICIOS.VIVENCIA) },
]

const seleccionarServicio = (servicio) => {
  console.log('Emitting servicio-seleccionado:', servicio)
  emit('servicio-seleccionado', servicio)
}
</script>

<style scoped>
.seleccion-servicio {
  height: 100%;
  width: 100%;
  display: flex;
  align-items: stretch;
}

.servicios-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 0;
  width: 100%;
  height: 100%;
}

.servicio-btn {
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  border: 2px solid rgba(0, 123, 255, 0.15);
  padding: var(--spacing-xl);
  text-align: center;
  cursor: pointer;
  transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
  font-size: var(--font-size-lg);
  font-weight: 600;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  box-shadow: 0 6px 18px rgba(51,153,255,0.12);
}

.servicio-btn:hover {
  transform: scale(1.02) translateY(-3px);
  filter: brightness(0.95);
  box-shadow: 0 10px 24px rgba(51,153,255,0.18);
}

.servicio-btn:nth-child(1) {
  border-right: 1px solid var(--color-light);
  border-bottom: 1px solid var(--color-light);
}

.servicio-btn:nth-child(2) {
  border-left: 1px solid var(--color-light);
  border-bottom: 1px solid var(--color-light);
}

.servicio-btn:nth-child(3) {
  border-right: 1px solid var(--color-light);
  border-top: 1px solid var(--color-light);
}

.servicio-btn:nth-child(4) {
  border-left: 1px solid var(--color-light);
  border-top: 1px solid var(--color-light);
}

.servicio-nombre {
  display: block;
  font-size: var(--font-size-xl);
  font-weight: bold;
  text-align: center;
  line-height: 1.3;
}
</style>
