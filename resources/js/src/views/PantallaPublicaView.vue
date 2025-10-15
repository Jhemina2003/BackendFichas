<template>
  <div class="pantalla-publica-view">
    <header class="pantalla-header">
      <div class="header-content">
        <img :src="logo" alt="Logo" class="header-logo" />
        <h1 class="header-title">
          <span class="header-line">MINISTERIO DE RELACIONES EXTERIORES</span>
          <span class="header-line">BIENVENIDOS</span>
        </h1>
      </div>
    </header>

    <main class="pantalla-main" v-if="sistemaActivo">
      <PantallaPublica />
    </main>

    <main class="pantalla-main" v-else>
      <div class="sistema-inactivo">
        <div class="inactivo-icono">🚫</div>
        <h2>Sistema Inactivo</h2>
        <p>El sistema se encuentra temporalmente inactivo.</p>
        <p>Espere a que un funcionario active el sistema.</p>
        <button @click="cerrarVentana" class="btn btn-primary mt-3">Cerrar Ventana</button>
      </div>
    </main>

    <footer class="pantalla-footer">
      <span class="footer-date">{{ fechaActual }}</span>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useSocketStore } from '../stores/socket.store'
import PantallaPublica from '../components/usuario/PantallaPublica.vue'
import logo from '../assets/images/logo.png'

const socketStore = useSocketStore()
const sistemaActivo = computed(() => socketStore.sistemaActivo)

const fechaActual = ref('')

const actualizarFecha = () => {
  const ahora = new Date()
  fechaActual.value = ahora.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}

const cerrarVentana = () => {
  window.close()
}

// CORREGIDO: Usar checkSystemState
onMounted(() => {
  socketStore.checkSystemState()
  actualizarFecha()

  const intervalo = setInterval(actualizarFecha, 1000)
  const storageInterval = setInterval(() => {
    socketStore.checkSystemState()
  }, 1000)

  onUnmounted(() => {
    clearInterval(intervalo)
    clearInterval(storageInterval)
  })
})
</script>

<style scoped>
.pantalla-publica-view {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: white;
}

.pantalla-header {
  background-color: white;
  color: black;
  padding: var(--spacing-lg);
  text-align: center;
  flex-shrink: 0;
  position: relative; /* permitir posicionar el logo respecto al header (viewport) */
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
  max-width: 1200px;
  margin: 0 auto;
}

.header-logo {
  height: 100px; /* ajustado a 100px */
  width: auto;
  /* place logo at the left edge of the header */
  position: absolute;
  left: var(--spacing-md);
  top: 50%;
  transform: translateY(-50%);
}

@media (max-width: 600px) {
  .header-logo {
    height: 48px; /* reducir en móviles */
  }
}

.header-title {
  font-size: var(--header-title-size);
  margin: 0;
  color: black;
}

.header-line {
  display: block;
  line-height: 1.05;
}

.pantalla-main {
  flex: 1;
  display: flex;
  align-items: stretch;
  justify-content: center;
  padding: 0;
  margin: 0;
  min-height: 0;
}

.pantalla-footer {
  background-color: white;
  color: black;
  padding: var(--spacing-lg);
  text-align: center;
  flex-shrink: 0;
  font-size: 2.8rem;
}

.footer-date {
  font-size: 2.4rem;
}

.sistema-inactivo {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  padding: var(--spacing-xl);
}

.inactivo-icono {
  font-size: 4rem;
  margin-bottom: var(--spacing-lg);
}
</style>
