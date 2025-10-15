<template>
  <button @click="logout" class="btn btn-logout btn-sm" title="Cerrar sesión">🚪</button>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth.store'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.btn-sm {
  padding: var(--spacing-md) var(--spacing-lg);
  font-size: 2rem;
  line-height: 1.2;
}

.btn-logout {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border: none;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

.btn-logout:hover {
  filter: brightness(0.95);
}
</style>
