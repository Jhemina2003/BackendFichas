<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">PRÓXIMOS TURNOS</h2>
      <div class="total-card" role="status" aria-label="Total">
        <div class="total-card-value">{{ colaTurnos.length }}</div>
        <div class="total-card-label">TOTAL</div>
      </div>
    </div>
    <div class="card-body">
      <div class="turnos-lista">
        <div
          v-for="turno in visibleTurnos"
          :key="turno.id"
          class="turno-item"
          :class="{ 'turno-preferencial': turno.tipo === 'PREFERENCIAL' }"
        >
          <span class="turno-codigo">{{ turno.codigo }}</span>
        </div>

        <div v-if="visibleTurnos.length === 0" class="sin-turnos">No hay turnos en espera</div>

        <div v-if="masCount > 0" class="mas-turnos">+{{ masCount }} más</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted, computed } from 'vue'
import { useTurnosStore } from '../../stores/turnos.store'

const turnosStore = useTurnosStore()
const { colaTurnos } = storeToRefs(turnosStore)

// Sincronización automática
const sincronizarTurnos = () => {
  turnosStore.sincronizarColaDesdeEventos()
}

// Mostrar sólo los primeros 3 turnos
const visibleTurnos = computed(() => {
  return colaTurnos.value ? colaTurnos.value.slice(0, 3) : []
})

const masCount = computed(() => {
  return colaTurnos.value && colaTurnos.value.length > 3
    ? colaTurnos.value.length - 3
    : 0
})

// Guardamos el id del intervalo en un scope superior para poder limpiarlo en onUnmounted
let intervalo = null

onMounted(() => {
  // Sincronizar al cargar
  sincronizarTurnos()

  // Escuchar eventos de storage para sincronización en tiempo real
  window.addEventListener('storage', manejarEventoStorage)

  // Sincronizar cada 2 segundos (fallback)
  intervalo = setInterval(sincronizarTurnos, 2000)
})

onUnmounted(() => {
  window.removeEventListener('storage', manejarEventoStorage)
  if (intervalo) {
    clearInterval(intervalo)
    intervalo = null
  }
})

const manejarEventoStorage = (event) => {
  if (event.key === 'eventosTurnos') {
    sincronizarTurnos()
  }
}
</script>

<style scoped>
.card {
  /* Reducir la altura unos píxeles: reste 24px de la altura completa de la celda */
  height: var(--small-section-height);
  display: flex;
  flex-direction: column;
}

.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0; /* importante para que children con flex:1 no desborden */
}

.turnos-lista {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  overflow: auto; /* permitir scroll interno si hay más turnos */
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-turnos {
  font-size: var(--font-size-sm);
  color: var(--color-secondary);
  font-weight: 500;
}

.total-card {
  background: white;
  border: 1px solid var(--color-light);
  padding: calc(var(--spacing-sm));
  border-radius: calc(var(--border-radius) + 4px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 90px;
}

.total-card-label {
  font-size: 0.75rem;
  color: var(--color-secondary);
  margin-top: 4px;
}

.total-card-value {
  font-weight: 900;
  font-size: 1.6rem;
  color: var(--color-primary);
}



.turno-item {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: var(--spacing-md);
  border: 1px solid var(--color-light);
  border-radius: var(--border-radius);
  background: white;
  text-align: center;
}

.turno-item.turno-preferencial {
  border-left: 4px solid var(--color-warning);
  background: #fff9e6;
}

.turno-codigo {
  font-weight: bold;
  color: var(--color-primary);
  font-size: var(--font-size-lg);
  text-align: center;
}

.sin-turnos {
  text-align: center;
  padding: var(--spacing-lg);
  color: var(--color-secondary);
  font-style: italic;
  background: var(--color-light);
  border-radius: var(--border-radius);
}

.mas-turnos {
  text-align: center;
  padding: var(--spacing-sm);
  color: var(--color-secondary);
  font-weight: 600;
  background: transparent;
}
</style>
