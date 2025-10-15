import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '../services/auth.service'
import { useTurnosStore } from './turnos.store'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role)

  const login = async (credentials) => {
    try {
      const response = await authService.login(credentials)
      user.value = response.user
      token.value = response.token
      localStorage.setItem('token', response.token)

      // Si es funcionario y es la primera vez (no hay flag en localStorage)
      if (response.user.role === 'funcionario') {
        const firstLoginKey = 'funcionario_first_login_done'
        if (!localStorage.getItem(firstLoginKey)) {
          // Reiniciar estadísticas y turnos
          const turnosStore = useTurnosStore()
          turnosStore.estadisticas.finalizados = 0
          turnosStore.estadisticas.ausentados = 0
          turnosStore.estadisticas.total = 0
          // Limpiar la cola reactiva y los eventos
          turnosStore.colaTurnos.splice(0, turnosStore.colaTurnos.length)
          localStorage.removeItem('eventosTurnos')
          localStorage.removeItem('eventosTurnosLlamados') // Limpiar también los turnos llamados para Pantalla Pública
          // Forzar sincronización para limpiar cualquier residuo
          if (typeof turnosStore.sincronizarColaDesdeEventos === 'function') {
            turnosStore.sincronizarColaDesdeEventos()
          }
          // Marcar que ya se hizo el primer login
          localStorage.setItem(firstLoginKey, 'true')
        }
      }
      return response
    } catch (error) {
      throw error
    }
  }

  const logout = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  const checkAuth = () => {
    // Simular verificación de token
    if (token.value) {
      user.value = {
        username: token.value.includes('funcionario') ? 'funcionario' : 'usuario',
        role: token.value.includes('funcionario') ? 'funcionario' : 'usuario',
      }
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    userRole,
    login,
    logout,
    checkAuth,
  }
})
