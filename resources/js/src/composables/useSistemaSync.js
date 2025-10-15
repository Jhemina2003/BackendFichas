import { ref, onMounted, onUnmounted } from 'vue'
import { useSocketStore } from '../stores/socket.store'

export const useSistemaSync = () => {
  const socketStore = useSocketStore()
  const sistemaActivo = ref(socketStore.sistemaActivo)

  const handleStorageChange = (event) => {
    if (event.key === 'sistemaActivo') {
      const nuevoEstado = event.newValue === 'true'
      sistemaActivo.value = nuevoEstado
      socketStore.setSistemaActivo(nuevoEstado)
      console.log('Sistema sync - Estado actualizado:', nuevoEstado ? 'ACTIVO' : 'INACTIVO')
    }
  }

  const handleCustomEvent = (event) => {
    sistemaActivo.value = event.detail.activo
    socketStore.setSistemaActivo(event.detail.activo)
    console.log('Sistema sync - Evento personalizado:', event.detail.activo ? 'ACTIVO' : 'INACTIVO')
  }

  onMounted(() => {
    // Escuchar cambios en localStorage
    window.addEventListener('storage', handleStorageChange)

    // Escuchar eventos personalizados
    window.addEventListener('sistemaStateChange', handleCustomEvent)

    // Sincronizar estado inicial
    const estadoGuardado = localStorage.getItem('sistemaActivo')
    if (estadoGuardado !== null) {
      sistemaActivo.value = estadoGuardado === 'true'
      socketStore.setSistemaActivo(estadoGuardado === 'true')
    }
  })

  onUnmounted(() => {
    window.removeEventListener('storage', handleStorageChange)
    window.removeEventListener('sistemaStateChange', handleCustomEvent)
  })

  return {
    sistemaActivo,
  }
}
