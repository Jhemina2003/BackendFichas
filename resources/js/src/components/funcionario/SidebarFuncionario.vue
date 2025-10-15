<template>
  <nav class="sidebar-funcionario">
    <ul class="sidebar-nav">
      <li class="sidebar-item">
        <button
          @click="iniciarSistema"
          class="sidebar-link btn-iniciar"
          :class="{ active: sistemaActivo }"
        >
          INICIAR
        </button>
      </li>
      <li class="sidebar-item">
        <button @click="cerrarSistema" class="sidebar-link btn-cerrar">CERRAR</button>
      </li>
      <li>
        <div class="sistema-status" :class="sistemaActivo ? 'activo' : 'inactivo'">
          Sistema: {{ sistemaActivo ? 'ACTIVO' : 'INACTIVO' }}
        </div>
      </li>
    </ul>
  </nav>
  <ModalConfirm
    v-if="showConfirm"
    title="Cerrar Sistema"
    message="¿Desea realmente cerrar el sistema? Esta acción desactivará el servicio para los usuarios."
    confirm-text="CERRAR"
    cancel-text="CANCELAR"
    confirm-variant="close"
    @confirmar="onConfirmClose"
    @cancelar="onCancelClose"
  />
  <ModalConfirm
    v-if="showStartConfirm"
    title="Iniciar Sistema"
    message="¿Desea iniciar el sistema? Esto permitirá que los usuarios utilicen el servicio."
    confirm-text="INICIAR"
    cancel-text="CANCELAR"
    confirm-variant="start"
    @confirmar="onConfirmStart"
    @cancelar="onCancelStart"
  />
</template>

<script setup>
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useSocketStore } from '../../stores/socket.store'
import ModalConfirm from './modales/ModalConfirm.vue'

const socketStore = useSocketStore()
const { sistemaActivo } = storeToRefs(socketStore)

const showConfirm = ref(false)
const showStartConfirm = ref(false)

// CORREGIDO: Usar las funciones del store
const iniciarSistema = () => {
  // abrir modal de confirmación para iniciar
  showStartConfirm.value = true
}

const onConfirmStart = () => {
  socketStore.iniciarSistema()
  showStartConfirm.value = false
}

const onCancelStart = () => {
  showStartConfirm.value = false
}

const cerrarSistema = () => {
  // abrir modal de confirmación
  showConfirm.value = true
}

const onConfirmClose = () => {
  // usuario confirmó cerrar el sistema
  socketStore.cerrarSistema()
  // Limpiar turnos llamados para vaciar pantalla pública
  localStorage.removeItem('eventosTurnosLlamados')
  window.dispatchEvent(
    new StorageEvent('storage', { key: 'eventosTurnosLlamados', newValue: null }),
  )
  showConfirm.value = false
}

const onCancelClose = () => {
  showConfirm.value = false
}
</script>

<style scoped>
.sidebar-funcionario {
  background-color: #fff;
  color: black;
  padding: var(--spacing-lg);
  height: 100%;
  min-height: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-sizing: border-box;
}

.sidebar-nav {
  list-style: none;
  flex: 1;
}

.sidebar-item {
  margin-bottom: var(--spacing-sm);
}

.sidebar-link {
  display: block;
  width: 100%;
  padding: var(--spacing-sm) var(--spacing-md);
  color: black;
  text-decoration: none;
  border: none;
  border-radius: var(--border-radius);
  background: none;
  cursor: pointer;
  text-align: left;
  font-size: var(--font-size-base);
  transition: background-color 0.3s ease;
}

.sidebar-link:hover,
.sidebar-link.active {
  background-color: rgba(0, 0, 0, 0.06);
}

.sistema-status {
  padding: var(--spacing-sm);
  text-align: center;
  border-radius: var(--border-radius);
  font-size: var(--font-size-sm);
  font-weight: bold;
  margin-top: var(--spacing-lg);
}

.sistema-status.activo {
  background-color: white;
  color: black;
  border: 2px solid var(--color-success);
}

.sistema-status.inactivo {
  background-color: white;
  color: black;
  border: 2px solid var(--color-danger);
}

/* Botones específicos */
.btn-iniciar {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  text-align: center;
}

.btn-cerrar {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  text-align: center;
}

.btn-iniciar:hover,
.btn-cerrar:hover {
  filter: brightness(0.95);
}
</style>
