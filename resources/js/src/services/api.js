const API_BASE_URL = 'http://localhost:3001/api'

export const api = {
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    }

    if (config.body) {
      config.body = JSON.stringify(config.body)
    }

    try {
      const response = await fetch(url, config)
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Error en la petición')
      }

      return data
    } catch (error) {
      throw error
    }
  },

  get(endpoint) {
    return this.request(endpoint)
  },

  post(endpoint, body) {
    return this.request(endpoint, {
      method: 'POST',
      body,
    })
  },

  put(endpoint, body) {
    return this.request(endpoint, {
      method: 'PUT',
      body,
    })
  },

  delete(endpoint) {
    return this.request(endpoint, {
      method: 'DELETE',
    })
  },
}
