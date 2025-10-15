<template>
  <div class="estado-llamado">
    <div class="botones-grid">
      <button @click="atenderTurno" class="btn btn-atender btn-lg">
        <span class="btn-content">
          <span class="btn-icon" aria-hidden="true">&#128100;</span>
          ATENDER TURNO
        </span>
      </button>
      <button @click="rellamarTurno" class="btn btn-rellamar btn-lg">
        <span class="btn-content">
          <span class="btn-icon" aria-hidden="true">🔁</span>
          RELLAMAR
        </span>
      </button>
      <button @click="marcarAusencia" class="btn btn-ausencia btn-lg">
        <span class="btn-content">
          <span class="btn-icon" aria-hidden="true">🚫</span>
          AUSENCIA
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useTurnosStore } from '../../../../stores/turnos.store'
// Icons replaced by emojis

const turnosStore = useTurnosStore()

const atenderTurno = () => {
  turnosStore.atenderTurno()
}

const rellamarTurno = () => {
  turnosStore.rellamarTurno()
}

const marcarAusencia = () => {
  turnosStore.marcarAusencia()
}
</script>

<style scoped>
.estado-llamado {
  text-align: center;
  width: 100%;
  box-sizing: border-box;
}

.botones-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--spacing-md);
  width: 100%;
}

.btn {
  border: none;
  font-weight: bold;
  font-size: var(--font-size-xl);
  padding: var(--spacing-xl) var(--spacing-2xl);
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  width: 100%;
  box-sizing: border-box;
  min-height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn:hover {
  transform: translateY(-2px);
}

.btn:active {
  transform: translateY(0);
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
}

.btn-icon {
  font-size: var(--font-size-2xl);
}

/* Botón ATENDER TURNO - Azul medio */
.btn-atender {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
}

.btn-atender:hover {
  box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6);
  background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
}

/* Botón RELLAMAR - Amarillo */
.btn-rellamar {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
  box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
}

.btn-rellamar:hover {
  box-shadow: 0 6px 20px rgba(255, 193, 7, 0.6);
  background: linear-gradient(135deg, #e0a800 0%, #ffc107 100%);
}

/* Botón AUSENCIA - Gris oscuro */
.btn-ausencia {
  background: linear-gradient(135deg, #495057 0%, #343a40 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(73, 80, 87, 0.4);
}

.btn-ausencia:hover {
  box-shadow: 0 6px 20px rgba(73, 80, 87, 0.6);
  background: linear-gradient(135deg, #343a40 0%, #495057 100%);
}
</style>
