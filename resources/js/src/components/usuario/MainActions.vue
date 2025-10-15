<template>
  <div class="main-actions">
    <button class="action-btn ticket" @click="abrirGenerarTicket">TICKET</button>
    <button class="action-btn pantalla" @click="abrirPantallaPublica">PANTALLA</button>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useSocketStore } from '../../stores/socket.store'
import { onMounted, onUnmounted } from 'vue'

const socketStore = useSocketStore()
const { sistemaActivo } = storeToRefs(socketStore)

// Check persisted state on mount and listen for storage updates from other windows
onMounted(() => {
  socketStore.checkSystemState()

  const handleStorage = (event) => {
    if (event.key === 'sistemaActivo') {
      const nuevo = event.newValue === 'true'
      socketStore.setSistemaActivo(nuevo)
    }
  }

  window.addEventListener('storage', handleStorage)

  onUnmounted(() => {
    window.removeEventListener('storage', handleStorage)
  })
})

const abrirGenerarTicket = () => {
  if (!sistemaActivo.value) {
    alert('El sistema se encuentra inactivo. Espere a que un funcionario active el sistema.')
    return
  }
  window.open(
    '/generar-ticket',
    'GenerarTicket',
    'width=800,height=700,menubar=no,toolbar=no,location=no',
  )
}

const abrirPantallaPublica = () => {
  if (!sistemaActivo.value) {
    alert('El sistema se encuentra inactivo. Espere a que un funcionario active el sistema.')
    return
  }
  window.open(
    '/pantalla-publica',
    'PantallaPublica',
    'width=1000,height=600,menubar=no,toolbar=no,location=no',
  )
}
</script>

<style scoped>
.main-actions {
  display: flex;
  gap: var(--spacing-xl);
  justify-content: center;
  align-items: center;
  height: 100%;
}

.action-btn {
  width: 50%;
  max-width: 640px;
  height: 400px; /* fixed height requested earlier */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 4rem; /* más grande */
  font-weight: 800; /* negrilla */
  border-radius: var(--border-radius);
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.12);
}

.action-btn.ticket {
  /* degradado azul difuminado - matches other service buttons */
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  color: #ffffff;
  border: 2px solid rgba(0, 123, 255, 0.12);
  box-shadow: 0 6px 18px rgba(51, 153, 255, 0.12);
}

.action-btn.pantalla {
  /* same visual style as TICKET */
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  color: #ffffff;
  border: 2px solid rgba(0, 123, 255, 0.12);
  box-shadow: 0 6px 18px rgba(51, 153, 255, 0.12);
}

.action-btn:hover {
  transform: translateY(-3px);
  filter: brightness(0.98);
  box-shadow: 0 10px 24px rgba(51, 153, 255, 0.18);
}

.action-btn:active {
  transform: translateY(0);
  filter: brightness(0.95);
}

.action-btn:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(51, 153, 255, 0.12);
}

@media (max-width: 900px) {
  .action-btn {
    width: 45%;
    height: 300px;
  }
}

@media (max-width: 600px) {
  .main-actions {
    flex-direction: column;
    padding: var(--spacing-lg);
  }
  .action-btn {
    width: 100%;
    max-width: none;
    height: 200px;
  }
}
</style>
