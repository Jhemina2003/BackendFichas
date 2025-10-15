<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">GESTIONAR TURNO</h2>
    </div>
    <div class="card-body">
      <component :is="estadoComponente" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useTurnosStore } from '../../../stores/turnos.store'
import EstadoInicial from './EstadosGestionTurno/EstadoInicial.vue'
import EstadoLlamado from './EstadosGestionTurno/EstadoLlamado.vue'
import EstadoAtencion from './EstadosGestionTurno/EstadoAtencion.vue'

const turnosStore = useTurnosStore()
const { estadoGestion } = storeToRefs(turnosStore)

const estadoComponente = computed(() => {
  switch (estadoGestion.value) {
    case 'llamando':
      return EstadoLlamado
    case 'atendiendo':
      return EstadoAtencion
    default:
      return EstadoInicial
  }
})
</script>

<style scoped>
.card {
  height: var(--small-section-height);
  display: flex;
  flex-direction: column;
}

.card-body {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 0;
}

</style>