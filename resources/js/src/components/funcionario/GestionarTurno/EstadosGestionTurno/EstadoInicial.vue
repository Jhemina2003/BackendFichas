<template>
  <div class="estado-inicial">
    <button @click="llamarTurno" class="btn btn-llamar btn-lg btn-block" :disabled="!sistemaActivo">
      <span class="btn-content">
        <span class="btn-icon" aria-hidden="true">📣</span>
        LLAMAR TURNO
      </span>
    </button>

    <div v-if="!sistemaActivo" class="sistema-inactivo-alerta">
      <p>El sistema está inactivo. Haga click en INICIAR para activar.</p>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useTurnosStore } from '../../../../stores/turnos.store'
import { useSocketStore } from '../../../../stores/socket.store'
// Icon replaced by emoji

const turnosStore = useTurnosStore()
const socketStore = useSocketStore()
const { sistemaActivo } = storeToRefs(socketStore)

const llamarTurno = () => {
  if (!sistemaActivo.value) {
    return
  }
  turnosStore.llamarTurno()
}
</script>

<style scoped>
.estado-inicial {
  text-align: center;
  padding: var(--spacing-lg) 0;
  width: 100%;
  box-sizing: border-box;
}

.btn-llamar {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  color: white;
  font-weight: bold;
  font-size: var(--font-size-xl);
  padding: var(--spacing-xl) var(--spacing-2xl);
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
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

.btn-llamar:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6);
  background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
}

.btn-llamar:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 10px rgba(40, 167, 69, 0.4);
}

.btn-llamar:disabled {
  background: #6c757d;
  box-shadow: none;
  transform: none;
  cursor: not-allowed;
  opacity: 0.7;
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
}

.btn-icon {
  font-size: var(--font-size-2xl);
  animation: pulse 2s infinite;
  display: inline-flex;
  align-items: center;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

.sistema-inactivo-alerta {
  margin-top: var(--spacing-md);
  padding: var(--spacing-sm);
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: var(--border-radius);
  color: #856404;
  font-size: var(--font-size-sm);
}
</style>
