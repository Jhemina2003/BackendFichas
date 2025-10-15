<template>
  <div class="descarga-ticket">
    <div class="ticket-generado text-center">
      <div class="ticket-icono mb-3">✅</div>
      <h3 class="mb-2">Ticket Generado Exitosamente</h3>
      <p class="text-muted mb-4">Su ticket ha sido descargado automáticamente</p>

      <div class="ticket-info card">
        <div class="ticket-codigo">{{ ticketInfo.codigo }}</div>
        <div class="ticket-servicio">{{ formatearServicio(ticketInfo.servicio) }}</div>
        <div class="ticket-tipo">{{ ticketInfo.tipo }}</div>
        <div class="ticket-fecha">{{ ticketInfo.fecha }}</div>
      </div>

      <div class="redireccion-mensaje mt-4">
        <p>Volviendo automáticamente a servicios...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatearServicio } from '../../../utils/helpers'

// Props para recibir la información del ticket
const props = defineProps({
  ticket: {
    type: Object,
    default: () => ({}),
  },
})

const ticketInfo = computed(() => ({
  codigo: props.ticket?.codigo || 'APOS.1', // Usar el código generado
  servicio: props.ticket?.servicio || 'APOSTILLA',
  tipo: props.ticket?.tipo || 'NORMAL',
  fecha: props.ticket?.fecha || new Date().toLocaleString('es-ES'),
}))
</script>

<style scoped>
.descarga-ticket {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-xl);
}

.ticket-generado {
  width: 100%;
  max-width: 400px;
}

.ticket-icono {
  font-size: 4rem;
}

.ticket-info {
  padding: var(--spacing-lg);
  margin: var(--spacing-md) auto;
  text-align: center;
  background: #f8f9fa;
  border: 2px solid var(--color-success);
}

.ticket-codigo {
  font-size: var(--font-size-2xl);
  font-weight: bold;
  color: var(--color-primary);
  margin-bottom: var(--spacing-sm);
}

.ticket-servicio {
  font-size: var(--font-size-lg);
  color: var(--color-dark);
  margin-bottom: var(--spacing-xs);
}

.ticket-tipo {
  font-size: var(--font-size-base);
  color: var(--color-secondary);
  margin-bottom: var(--spacing-sm);
}

.ticket-fecha {
  font-size: var(--font-size-sm);
  color: var(--color-secondary);
}

.text-muted {
  color: var(--color-secondary);
}

.redireccion-mensaje {
  color: var(--color-primary);
  font-style: italic;
}
</style>
