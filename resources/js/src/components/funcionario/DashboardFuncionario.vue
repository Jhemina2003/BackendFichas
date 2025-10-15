<template>
  <div class="dashboard-funcionario">
    <div class="dashboard-grid">
      <div class="dashboard-cell estadisticas-col"><Estadisticas /></div>
      <div class="dashboard-cell proximos-col"><ProximosTurnos /></div>
      <div class="dashboard-cell gestion-col"><GestionarTurno /></div>
      <div class="dashboard-cell turno-curso-col"><TurnoEnCurso /></div>
    </div>
  </div>
</template>

<script setup>
import Estadisticas from './Estadisticas.vue'
import GestionarTurno from './GestionarTurno/index.vue'
import TurnoEnCurso from './TurnoEnCurso.vue'
import ProximosTurnos from './ProximosTurnos.vue'
</script>

<style scoped>
.dashboard-funcionario {
  /* Reducir padding superior para subir el grid */
  padding: 0 var(--spacing-md);
  height: 100%;
}
.dashboard-grid {
  /* Grid 2x2 con filas automáticas para que la segunda fila quede pegada a la primera */
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto;
  column-gap: var(--spacing-md);
  row-gap: 0; /* filas pegadas */
  height: auto;
  max-width: 1400px;
  margin: 0 auto; /* centrar horizontalmente */
  align-content: start;
}

.dashboard-cell {
  display: flex;
  flex-direction: column;
  background: transparent;
  /* cada celda puede tener overflow interno si su contenido es mayor */
  overflow: auto;
  align-self: start; /* asegurar que las celdas no se estiren verticalmente */
}

/* Para pantallas pequeñas, apilar en una columna */
@media (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
    grid-template-rows: auto;
    height: auto;
  }
}
</style>