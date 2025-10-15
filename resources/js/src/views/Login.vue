<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <img :src="logo" alt="Logo" class="auth-logo" />
        <h1 class="auth-title">SISTEMA DE TICKETS</h1>
        <p class="auth-subtitle">Ministerio de Relaciones Exteriores</p>
      </div>

      <form @submit.prevent="login" class="auth-form">
        <div class="form-group">
          <label class="form-label">Usuario</label>
          <input
            v-model="credentials.username"
            type="text"
            class="form-control"
            required
            placeholder="Ingrese su usuario"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Contraseña</label>
          <input
            v-model="credentials.password"
            type="password"
            class="form-control"
            required
            placeholder="Ingrese su contraseña"
          />
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>
      </form>

      <div class="auth-footer">
        <p>Use: funcionario/funcionario o usuario/usuario</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import logo from '../assets/images/logo.png'

const router = useRouter()
const authStore = useAuthStore()

const credentials = ref({
  username: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

const login = async () => {
  try {
    loading.value = true
    error.value = ''

    await authStore.login(credentials.value)

    // Redirigir según el rol
    const redirectPath = authStore.user.role === 'funcionario' ? '/funcionario' : '/usuario'
    router.push(redirectPath)
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.error-message {
  background-color: #fee;
  color: var(--color-danger);
  padding: var(--spacing-sm);
  border-radius: var(--border-radius);
  margin-bottom: var(--spacing-md);
  border: 1px solid var(--color-danger);
  font-size: var(--font-size-sm);
}

/* Fondo azul difuminado para la pantalla de login */
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    radial-gradient(
      ellipse at top left,
      rgba(58, 123, 213, 0.85) 0%,
      rgba(0, 86, 179, 0.75) 40%,
      rgba(0, 43, 91, 0.65) 100%
    ),
    linear-gradient(180deg, rgba(58, 123, 213, 0.35), rgba(0, 86, 179, 0.25));
  background-color: #3a7bd5; /* fallback */
  padding: var(--spacing-lg);
}

.auth-card {
  width: 100%;
  max-width: 520px;
  background: rgba(255, 255, 255, 0.98);
  border-radius: calc(var(--border-radius) * 1.25);
  box-shadow: 0 12px 40px rgba(2, 6, 23, 0.3);
  padding: var(--spacing-lg);
}

.auth-header {
  text-align: center;
  margin-bottom: var(--spacing-lg);
}
.auth-logo {
  height: 64px;
  width: auto;
  margin-bottom: var(--spacing-sm);
}
.auth-title {
  margin: 0;
  font-size: 1.4rem;
}
.auth-subtitle {
  margin: 0;
  color: rgba(0, 0, 0, 0.6);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}
.auth-footer {
  text-align: center;
  margin-top: var(--spacing-md);
  color: rgba(255, 255, 255, 0.85);
}

/* Botón principal azul difuminado */
.btn-primary {
  background: linear-gradient(
    135deg,
    rgba(58, 123, 213, 1) 0%,
    rgba(0, 86, 179, 1) 50%,
    rgba(0, 43, 91, 1) 100%
  );
  color: white;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: var(--border-radius);
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 6px 18px rgba(2, 6, 23, 0.12);
  transition:
    transform 0.12s ease,
    box-shadow 0.12s ease,
    filter 0.12s ease;
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 10px 30px rgba(2, 6, 23, 0.14);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  filter: none;
}

@media (max-width: 600px) {
  .auth-card {
    padding: var(--spacing-md);
  }
  .auth-logo {
    height: 48px;
  }
}
</style>
