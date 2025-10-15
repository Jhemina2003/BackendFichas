import { ref } from 'vue'
import { useSocketStore } from '../stores/socket.store'

export const useWebSocket = () => {
  const socketStore = useSocketStore()

  const connect = () => {
    socketStore.connect()
  }

  const disconnect = () => {
    socketStore.disconnect()
  }

  const emit = (event, data) => {
    socketStore.emit(event, data)
  }

  return {
    isConnected: socketStore.isConnected,
    connect,
    disconnect,
    emit,
  }
}
