import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useSocketStore = defineStore('socket', () => {
  const isConnected = ref(false)
  const lastMessage = ref(null)
  const sistemaActivo = ref(false)

  const connect = () => {
    isConnected.value = true
    sistemaActivo.value = true
    console.log('WebSocket conectado - Sistema ACTIVADO')
    localStorage.setItem('sistemaActivo', 'true')
  }

  const disconnect = () => {
    isConnected.value = false
    sistemaActivo.value = false
    console.log('WebSocket desconectado - Sistema DESACTIVADO')
    localStorage.setItem('sistemaActivo', 'false')
  }

  const iniciarSistema = () => {
    connect()
  }

  const cerrarSistema = () => {
    disconnect()
  }

  const checkSystemState = () => {
    const estado = localStorage.getItem('sistemaActivo') === 'true'
    sistemaActivo.value = estado
    isConnected.value = estado
    console.log('Estado del sistema verificado:', estado)
    return estado
  }

  // Allow external code (storage handlers) to set the system state in this store
  const setSistemaActivo = (valor) => {
    const estado = Boolean(valor)
    sistemaActivo.value = estado
    isConnected.value = estado
    // persist for other windows
    try {
      localStorage.setItem('sistemaActivo', estado ? 'true' : 'false')
    } catch (err) {
      console.warn('No se pudo escribir en localStorage:', err)
    }
    console.log('setSistemaActivo called:', estado)
    return estado
  }

  return {
    isConnected,
    lastMessage,
    sistemaActivo,
    connect,
    disconnect,
    iniciarSistema,
    cerrarSistema,
    checkSystemState,
    setSistemaActivo,
  }
})
