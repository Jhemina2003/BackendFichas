export const socketService = {
  connect() {
    // En una implementación real, conectaría con Socket.IO
    console.log('Conectando al servidor WebSocket...')
    return {
      on: (event, callback) => console.log(`Escuchando evento: ${event}`),
      emit: (event, data) => console.log(`Emitting ${event}:`, data),
      disconnect: () => console.log('Desconectado'),
    }
  },
}
