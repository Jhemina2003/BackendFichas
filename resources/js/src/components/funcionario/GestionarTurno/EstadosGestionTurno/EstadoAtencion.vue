<template>
  <div class="estado-atencion">
    <div class="botones-grid">
      <button @click="finalizarTurno" class="btn btn-finalizar btn-lg">
        <span class="btn-content">
          <span class="btn-icon" aria-hidden="true">✅</span>
          FINALIZAR TURNO
        </span>
      </button>
      <button @click="abrirModalObservacion" class="btn btn-observacion btn-lg">
        <span class="btn-content">
          <span class="btn-icon" aria-hidden="true">💬</span>
          OBSERVACIÓN
        </span>
      </button>
      <button @click="abrirModalRetornar" class="btn btn-redirigir btn-lg">
        <span class="btn-content">
          <span class="btn-icon" aria-hidden="true">➡️</span>
          RETORNAR
        </span>
      </button>
    </div>

    <ModalObservacion
      v-if="modals.observacion"
      @guardar="guardarObservacion"
      @cancelar="cerrarModalObservacion"
    />

    <ModalRetornar
      v-if="modals.retornar"
      @guardar="guardarRetorno"
      @cancelar="cerrarModalRetornar"
    />
  </div>
</template>

<script setup>
import { useTurnosStore } from '../../../../stores/turnos.store'
import { useUIStore } from '../../../../stores/ui.store'
import ModalObservacion from '../../modales/ModalObservacion.vue'
import ModalRetornar from '../../modales/ModalRetornar.vue'
// Icons replaced by emojis

const turnosStore = useTurnosStore()
const uiStore = useUIStore()
const { modals } = uiStore

const finalizarTurno = () => {
  turnosStore.finalizarTurno()
}

const abrirModalObservacion = () => {
  uiStore.openModal('observacion')
}

const abrirModalRedirigir = () => {
  uiStore.openModal('redirigir')
}

const cerrarModalObservacion = () => {
  uiStore.closeModal('observacion')
}

const abrirModalRetornar = () => {
  uiStore.openModal('retornar')
}

const cerrarModalRetornar = () => {
  uiStore.closeModal('retornar')
}

const guardarRetorno = (justificativo) => {
  turnosStore.retornarTurno(justificativo)
  uiStore.closeModal('retornar')
}
  uiStore.closeModal('redirigir')

</script>

<style scoped>
.estado-atencion {
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

/* Botón FINALIZAR TURNO - Rojo */
.btn-finalizar {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
}

.btn-finalizar:hover {
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.6);
  background: linear-gradient(135deg, #c82333 0%, #dc3545 100%);
}

/* Botón OBSERVACIÓN - Morado */
.btn-observacion {
  background: linear-gradient(135deg, #6f42c1 0%, #5a2d9c 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(111, 66, 193, 0.4);
}

.btn-observacion:hover {
  box-shadow: 0 6px 20px rgba(111, 66, 193, 0.6);
  background: linear-gradient(135deg, #5a2d9c 0%, #6f42c1 100%);
}

/* Botón REDIRIGIR - Naranja */
.btn-redirigir {
  background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(253, 126, 20, 0.4);
}

.btn-redirigir:hover {
  box-shadow: 0 6px 20px rgba(253, 126, 20, 0.6);
  background: linear-gradient(135deg, #e55a00 0%, #fd7e14 100%);
}
</style>
