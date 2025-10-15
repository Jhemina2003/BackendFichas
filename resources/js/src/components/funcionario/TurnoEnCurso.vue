<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">TURNO EN CURSO</h2>
    </div>
    <div class="card-body">
      <div class="turno-actual" v-if="turnoEnCurso">
        <p class="turno-codigo">{{ turnoEnCurso.codigo }}</p>
        <p class="turno-servicio">{{ formatearServicio(turnoEnCurso.servicio) }}</p>
        <p class="turno-tipo" :class="turnoEnCurso.tipo.toLowerCase()">
          {{ turnoEnCurso.tipo }}
        </p>
      </div>
      <div class="sin-turno" v-else>
        <p class="text-center">NINGUNO</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useTurnosStore } from '../../stores/turnos.store'
import { formatearServicio } from '../../utils/helpers'

const turnosStore = useTurnosStore()
const { turnoEnCurso } = storeToRefs(turnosStore)
</script>

<style scoped>
.turno-actual {
  text-align: center;
  /* Llenar completamente la tarjeta y centrar el contenido */
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
  padding: 0 var(--spacing-md);
}

/* Asegurar que la tarjeta ocupe toda la altura de la celda y que el body se estire
   de manera consistente con `GestionarTurno` */
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

.turno-codigo {
  /* Reducir tamaño del texto principal para TURNO EN CURSO */
  font-size: clamp(1.5rem, 6vw, 4rem);
  font-weight: bold;
  color: var(--color-success);
  margin-bottom: var(--spacing-sm);
}

.turno-servicio {
  color: var(--color-secondary);
  font-size: clamp(0.9rem, 2.8vw, 1.4rem);
  margin-bottom: var(--spacing-sm);
}

.turno-tipo {
  display: inline-block;
  padding: calc(var(--spacing-xs) + 2px) calc(var(--spacing-sm) + 4px);
  border-radius: var(--border-radius);
  font-size: clamp(0.8rem, 2.2vw, 1rem);
  font-weight: bold;
  text-transform: uppercase;
}

.turno-tipo.normal {
  background-color: var(--color-primary);
  color: white;
}

.turno-tipo.preferencial {
  background-color: var(--color-warning);
  color: white;
}

.sin-turno {
  text-align: center;
  /* Centrar y reducir el tamaño del texto cuando no hay turno */
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 var(--spacing-md);
  color: var(--color-secondary);
  font-style: italic;
  font-size: clamp(1rem, 3.5vw, 1.75rem);
}
</style>
