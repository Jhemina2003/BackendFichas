import { ref } from 'vue'
import { useAuthStore } from '../stores/auth.store'

export const useAuth = () => {
  const authStore = useAuthStore()

  const login = async (credentials) => {
    return await authStore.login(credentials)
  }

  const logout = () => {
    authStore.logout()
  }

  return {
    user: authStore.user,
    isAuthenticated: authStore.isAuthenticated,
    login,
    logout,
  }
}
