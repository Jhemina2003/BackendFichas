<template>
  <footer class="footer">
    <div class="footer-content">
      <span class="footer-date">{{ fechaActual }}</span>
    </div>
  </footer>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

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

let intervalo

onMounted(() => {
  actualizarFecha()
  intervalo = setInterval(actualizarFecha, 1000)
})

onUnmounted(() => {
  clearInterval(intervalo)
})
</script>

<style scoped>
/* Footer limpio y simple */
.footer {
  background-color: #fff;
  color: #222;
  padding: 16px 0;
  margin-top: auto;
  border-top: 1px solid #eee;
}
.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 16px;
  text-align: center;
}
.footer-date {
  font-size: 0.95rem;
  letter-spacing: 0.02em;
}
</style>
