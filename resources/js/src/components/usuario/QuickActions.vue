<template>
  <div class="quick-actions">
    <div class="opciones-principales">
      <div
        class="opcion-principal"
        @click="abrirGenerarTicket"
        role="button"
        aria-label="Generar ticket"
      >
        <div class="opcion-icono" aria-hidden="true">🎫</div>
        <h3 class="opcion-titulo">
          <span class="titulo-emoji" aria-hidden="true">🎫</span> TICKET
        </h3>
        <p class="opcion-descripcion">Generar nuevo ticket</p>
      </div>
      <div
        class="opcion-principal"
        @click="abrirPantallaPublica"
        role="button"
        aria-label="Abrir pantalla pública"
      >
        <div class="opcion-icono" aria-hidden="true">📺</div>
        <h3 class="opcion-titulo">
          <span class="titulo-emoji" aria-hidden="true">📺</span> PANTALLA
        </h3>
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
.quick-actions {
  width: 100%;
  padding: var(--spacing-xl);
  box-sizing: border-box;
  background: transparent;
}

.opciones-principales {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-xl);
  align-items: stretch;
}

.opcion-principal {
  /* tarjeta con degradado azul para destacar TICKET/PANTALLA */
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  padding: var(--spacing-xl);
  border-radius: var(--border-radius);
  text-align: center;
  box-shadow: 0 6px 18px rgba(51, 153, 255, 0.12);
  cursor: pointer;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    filter 0.18s ease;
  border: 2px solid rgba(0, 123, 255, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.opcion-icono {
  margin-bottom: var(--spacing-md);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  /* colocar el emoji arriba del título: tamaño grande y centrado */
  font-size: 4.8rem;
  line-height: 1;
}

/* ajustar el svg interno (la mayoría de los iconos usan .icon con fill=currentColor) */
.opcion-icono .icon,
.opcion-icono svg {
  width: 3.2rem;
  height: 3.2rem;
  color: #ffffff; /* controla el color via currentColor */
}
.opcion-titulo {
  font-size: var(--font-size-xl);
  color: #ffffff;
  margin-bottom: var(--spacing-sm);
}
.opcion-descripcion {
  color: rgba(255, 255, 255, 0.9);
  font-size: var(--font-size-base);
}

.opcion-principal:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(51, 153, 255, 0.18);
  filter: brightness(1.02);
}

.opcion-principal:active {
  transform: translateY(-1px);
}

@media (max-width: 600px) {
  .opciones-principales {
    grid-template-columns: 1fr;
  }
  .opcion-icono {
    font-size: 3rem;
  }
}
</style>
