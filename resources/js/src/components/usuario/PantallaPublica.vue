<template>
  <div class="pantalla-publica">
    <div class="pantalla-container">
      <div class="pantalla-contenido">
        <table class="tabla-turnos">
          <thead>
            <tr>
              <th>TICKET</th>
              <th>VENTANILLA</th>
            </tr>
          </thead>
          <tbody>
            <!-- Render exactly 4 data rows (so header + 4 = 5 filas total) -->
            <tr v-for="(fila, idx) in tablaFilas" :key="idx">
              <td
                class="ticket-codigo"
                :class="{ 'ticket-llamado': idx === 0 && fila && parpadeoActivo }"
              >{{ fila ? fila.codigo : '' }}</td>
              <td
                class="ventanilla-numero"
                :class="{ 'ticket-llamado': idx === 0 && fila && parpadeoActivo }"
              >{{ fila ? fila.ventanilla : '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

// Usamos ref local en lugar del store para mayor control
const turnosEnPantalla = ref([])

// --- VOZ AUTOMÁTICA AL LLAMAR TICKET ---
let ultimoTicketLlamado = ''

const reproducirVozTicket = (ticket) => {
  if (!ticket) return
  // Activar parpadeo al llamar turno
  localStorage.setItem('parpadeoTicketLlamado', 'true')
  window.dispatchEvent(new StorageEvent('storage', { key: 'parpadeoTicketLlamado', newValue: 'true' }))
  // Construir el mensaje de voz
  const mensaje = `TICKET ${ticket.codigo.replace('.', ' ')} PASAR A VENTANILLA ${ticket.ventanilla}`
  const utter = new window.SpeechSynthesisUtterance(mensaje)
  utter.lang = 'es-ES'
  utter.rate = 1; // velocidad normal
  utter.pitch = 1; // tono normal
  utter.volume = 1; // volumen normal
  utter.onend = () => {
    // Al terminar la voz, desactivar el parpadeo
    localStorage.setItem('parpadeoTicketLlamado', 'false')
    window.dispatchEvent(new StorageEvent('storage', { key: 'parpadeoTicketLlamado', newValue: 'false' }))
  }
  window.speechSynthesis.speak(utter)
}

// Mostrar exactamente 4 filas de datos en la tabla (header + 4 = 5 filas)
const VISIBLE_ROWS = 4
const tablaFilas = computed(() => {
  const datos = turnosEnPantalla.value.slice(0, VISIBLE_ROWS)
  const filas = []
  for (let i = 0; i < VISIBLE_ROWS; i++) {
    filas.push(datos[i] || null)
  }
  return filas
})

// Estado para controlar si el ticket debe parpadear
const parpadeoActivo = ref(true)

// Leer el estado de parpadeo desde localStorage
const leerParpadeo = () => {
  const valor = localStorage.getItem('parpadeoTicketLlamado')
  parpadeoActivo.value = valor !== 'false'
}

// Función para obtener turnos llamados desde localStorage
const obtenerTurnosLlamados = () => {
  try {
    const eventos = JSON.parse(localStorage.getItem('eventosTurnosLlamados') || '[]')
    // Mostrar hasta 4 tickets llamados, sin duplicados y en orden de llegada
    const vistos = new Set()
    const turnos = []
    let ultimoEvento = null
    for (const evento of eventos) {
      if (evento.type === 'TURNO_LLAMADO' && evento.data && !vistos.has(evento.data.id)) {
        turnos.push(evento.data)
        vistos.add(evento.data.id)
        if (!ultimoEvento) ultimoEvento = evento
      }
      if (turnos.length >= 4) break; // Solo los 4 más recientes
    }
    // Reproducir voz y activar parpadeo si el ticket cambia o si el evento es nuevo
    if (turnos[0] && (turnos[0].codigo !== ultimoTicketLlamado || (ultimoEvento && ultimoEvento.timestamp !== window.__ultimoEventoLlamado))) {
      reproducirVozTicket(turnos[0])
      ultimoTicketLlamado = turnos[0].codigo
      window.__ultimoEventoLlamado = ultimoEvento ? ultimoEvento.timestamp : null
    }
    turnosEnPantalla.value = turnos
    leerParpadeo()
  } catch (error) {
    console.error('Error obteniendo turnos:', error)
  }
}

// Sincronización automática
const sincronizarTurnos = () => {
  obtenerTurnosLlamados()
}

onMounted(() => {
  console.log('PantallaPublica montada')

  // Sincronizar al cargar
  sincronizarTurnos()

  // Escuchar eventos de storage
  const manejarEventoStorage = (event) => {
    if (event.key === 'eventosTurnosLlamados') {
      console.log('Nuevo turno detectado, sincronizando...')
      sincronizarTurnos()
    }
    if (event.key === 'parpadeoTicketLlamado') {
      leerParpadeo()
    }
  }

  window.addEventListener('storage', manejarEventoStorage)

  // Sincronizar cada segundo
  const intervalo = setInterval(sincronizarTurnos, 1000)

  // Activar parpadeo al cargar si corresponde
  leerParpadeo()

  onUnmounted(() => {
    window.removeEventListener('storage', manejarEventoStorage)
    clearInterval(intervalo)
  })
})
</script>

<style scoped>
.pantalla-publica {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px;
  box-sizing: border-box;
}

.pantalla-container {
  width: 100%;
  max-width: 1400px;
  display: flex;
  flex-direction: column;
  /* let parent layout control the height (layouts use min-height:100vh) */
  flex: 1 1 auto;
}

.pantalla-contenido {
  width: 100%;
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  overflow: auto; /* allow internal scrolling if content exceeds available area */
}

.tabla-turnos {
  width: 92%; /* make the table occupy most of the available width */
  margin: 0 auto;
  border-collapse: collapse;
  background: #fff;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  border-radius: 8px;
  overflow: hidden;
  display: table;
}

.tabla-turnos thead th {
  text-align: center;
  padding: 18px 20px;
  /* Blue gradient for header */
  background: linear-gradient(135deg, #007bff 0%, #00a3ff 100%);
  color: #fff;
  font-size: 5rem; /* enlarged header to match bigger content */
  font-weight: 700;
}

.tabla-turnos tbody td {
  padding: 8px 12px; /* slightly reduced padding for compactness */
  font-size: 5rem; /* increased for better visibility */
  text-align: center; /* center content */
  vertical-align: middle;
}

.tabla-turnos tbody {
  display: table-row-group;
}

.tabla-turnos tbody tr {
  height: auto; /* let rows size to their content */
}

.ticket-codigo {
  width: 70%; /* give ticket more space */
  font-weight: 700;
}

/* Animación para el ticket llamado */
.ticket-llamado {
  background: linear-gradient(90deg, #43ea6b 0%, #1ecb4f 100%);
  color: #111 !important;
  animation: parpadeo 1.2s linear infinite;
  font-weight: 900;
  box-shadow: 0 0 18px 2px #43ea6b;
}

@keyframes parpadeo {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.ventanilla-numero {
  width: 30%;
  text-align: center;
  font-size: 5rem;
  font-weight: 600;
}

.sin-turnos {
  text-align: center;
  padding: 28px;
  color: #666;
}

@media (max-width: 768px) {
  .tabla-turnos {
    width: 100%;
  }
  .ticket-codigo, .ventanilla-numero {
    font-size: 3.2rem;
  }
}
</style>
