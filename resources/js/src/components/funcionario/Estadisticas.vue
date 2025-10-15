<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">ESTADISTICAS</h2>
    </div>
    <div class="card-body">
      <div class="estadisticas-grid">
        <div class="estadistica-item">
          <span class="estadistica-valor">{{ estadisticas.finalizados }}</span>
          <span class="estadistica-label">FINALIZADOS</span>
        </div>
        <div class="estadistica-item">
          <span class="estadistica-valor">{{ estadisticas.ausentados }}</span>
          <span class="estadistica-label">AUSENTADOS</span>
        </div>
        <div class="estadistica-item">
          <span class="estadistica-valor">{{ estadisticas.total }}</span>
          <span class="estadistica-label">TOTAL</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useTurnosStore } from '../../stores/turnos.store'

const turnosStore = useTurnosStore()
const { estadisticas } = storeToRefs(turnosStore)
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
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.estadisticas-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--spacing-md);
}

.estadistica-item {
  background: white;
  padding: calc(var(--spacing-lg) + 0.5rem);
  border-radius: calc(var(--border-radius) + 4px);
  text-align: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  border: 2px solid var(--color-light);
  min-height: 120px;
}

.estadistica-valor {
  /* Aumentar tamaño de los números y hacerlo responsive */
  font-size: clamp(1.75rem, 5vw, 3rem);
  font-weight: 800;
  color: var(--color-primary);
  display: block;
  margin-bottom: calc(var(--spacing-xs) + 2px);
}

.estadistica-label {
  font-size: clamp(0.9rem, 2.2vw, 1.1rem);
  color: var(--color-secondary);
  font-weight: 600;
}
</style>
