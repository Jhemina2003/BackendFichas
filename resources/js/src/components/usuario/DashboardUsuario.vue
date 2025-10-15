<template>
  <div class="dashboard-usuario">
    <div class="opciones-principales">
      <div class="opcion-principal" @click="abrirGenerarTicket">
        <div class="opcion-icono">🎫</div>
        <h3 class="opcion-titulo">TICKET</h3>
        <p class="opcion-descripcion">Generar nuevo ticket</p>
      </div>
      <div class="opcion-principal" @click="abrirPantallaPublica">
        <div class="opcion-icono">📺</div>
        <h3 class="opcion-titulo">PANTALLA</h3>
        <p class="opcion-descripcion">Ver turnos en pantalla</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted } from 'vue'
import { useSocketStore } from '../../stores/socket.store'

const socketStore = useSocketStore()
const { sistemaActivo } = storeToRefs(socketStore)

// CORREGIDO: Usar checkSystemState
onMounted(() => {
  socketStore.checkSystemState()

  window.addEventListener('storage', sincronizarEstado)
})

onUnmounted(() => {
  window.removeEventListener('storage', sincronizarEstado)
})

const sincronizarEstado = (event) => {
  if (event.key === 'sistemaActivo') {
    const estado = event.newValue === 'true'
    sistemaActivo.value = estado
    console.log('Estado del sistema sincronizado:', estado)
  }
}

const abrirGenerarTicket = () => {
  if (!sistemaActivo.value) {
    alert('El sistema se encuentra inactivo. Espere a que un funcionario active el sistema.')
    return
  }
  window.open(
    '/generar-ticket',
    'GenerarTicket',
    'width=800,height=700,menubar=no,toolbar=no,location=no',
  )
}

const abrirPantallaPublica = () => {
  if (!sistemaActivo.value) {
    alert('El sistema se encuentra inactivo. Espere a que un funcionario active el sistema.')
    return
  }
  window.open(
    '/pantalla-publica',
    'PantallaPublica',
    'width=1000,height=600,menubar=no,toolbar=no,location=no',
  )
}
</script>

<style scoped>
.dashboard-usuario {
  /* Use flex to fill the parent area (usuario-main is flex:1) so footer stays just after content */
  max-width: none;
  width: 100%;
  margin: 0;
  padding: var(--spacing-xl);
  display: flex;
  flex-direction: column;
  flex: 1; /* fill available vertical space provided by the layout */
  align-items: stretch;
}


.opciones-principales {
  display: grid;
  /* two equal flexible columns so each option occupies 50% of the content area */
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-xl);
  margin-bottom: var(--spacing-xl);
  align-items: stretch; /* ensure children stretch vertically */
  height: 100%; /* take full height of dashboard container */
  width: 100%;
  flex: 1; /* fill remaining vertical space so options stretch between header/footer */
}

.opcion-principal {
  background: white;
  padding: var(--spacing-xl);
  border-radius: var(--border-radius);
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
  border: 2px solid transparent;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center; /* center content vertically */
  width: 100%;
  height: 100%;
  min-height: 0; /* allow flex children to shrink properly */
}

.opcion-principal:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  border-color: var(--color-primary);
}

.opcion-icono {
  font-size: 3rem;
  margin-bottom: var(--spacing-md);
}

.opcion-titulo {
  font-size: var(--font-size-xl);
  color: var(--color-primary);
  margin-bottom: var(--spacing-sm);
}

.opcion-descripcion {
  color: var(--color-secondary);
  font-size: var(--font-size-base);
}

/* Responsive: on narrow screens stack them */
/* stack the buttons on narrower viewports (when two 640px columns won't fit) */
@media (max-width: 1320px) {
  .opciones-principales {
    grid-template-columns: 1fr;
    height: auto;
  }
}
</style>
