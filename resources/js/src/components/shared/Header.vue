<template>
  <header class="header">
    <div class="header-content">
      <div class="header-logo">
        <img :src="logo" alt="Logo" class="logo" />
        <h1 class="header-title">SISTEMA DE TICKETS</h1>
      </div>
      <div class="header-user">
        <img :src="userAvatar" alt="Usuario" class="user-avatar" />
        <span class="user-name">{{ user?.username }}</span>
        <LogoutButton />
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../stores/auth.store'
import LogoutButton from './LogoutButton.vue'
import logo from '../../assets/images/logo.png'
import userAvatar from '../../assets/images/user-avatar.png'

const authStore = useAuthStore()
const user = computed(() => authStore.user)
</script>

<style scoped>
.header {
  /* make header background white across the app */
  background-color: white;
  color: black;
  padding: var(--spacing-md) 0;
  box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  /* ocupar todo el ancho para que los elementos puedan ir a las esquinas */
  width: 100%;
  max-width: none;
  margin: 0;
  padding: 0 var(--spacing-md);
}

.header-logo {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.logo {
  height: 100px;
  width: auto;
}

@media (max-width: 600px) {
  .logo {
    height: 48px;
  }
}

.header-title {
  font-size: var(--header-title-size);
  margin: 0;
  color: black; /* explícito para garantizar título en negro */
}

.header-user {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.user-avatar {
  height: 32px;
  width: 32px;
  border-radius: 50%;
  border: 2px solid rgba(0, 0, 0, 0.08);
}

.user-name {
  font-weight: 500;
  color: black;
}
</style>
