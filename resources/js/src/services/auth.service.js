import { api } from './api'

export const authService = {
  async login(credentials) {
    // Simular login - en producción esto haría una petición real
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        if (
          (credentials.username === 'funcionario' && credentials.password === 'funcionario') ||
          (credentials.username === 'usuario' && credentials.password === 'usuario')
        ) {
          resolve({
            user: {
              username: credentials.username,
              role: credentials.username,
            },
            token: `token_${credentials.username}_${Date.now()}`,
          })
        } else {
          reject(new Error('Credenciales incorrectas'))
        }
      }, 1000)
    })
  },

  async logout() {
    // Simular logout
    return Promise.resolve()
  },
}
