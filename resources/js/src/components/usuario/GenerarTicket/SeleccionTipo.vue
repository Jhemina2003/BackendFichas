<template>
  <div class="seleccion-tipo">
    <div class="tipos-grid">
      <button
        v-for="tipo in tipos"
        :key="tipo.key"
        @click="seleccionarTipo(tipo.key)"
        class="tipo-btn"
      >
        <div class="tipo-inner">
          <template v-if="tipo.key === 'NORMAL' || tipo.key === TIPOS_TURNO.NORMAL">
            <span class="tipo-icon"><component :is="IconPersona" class="icon-svg" /></span>
          </template>
          <template v-else>
            <span class="tipo-icons-line"><component :is="IconPreferencialSet" class="icon-svg" /></span>
          </template>
          <span class="tipo-nombre">{{ tipo.nombre }}</span>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { TIPOS_TURNO } from '../../../utils/constants'

const emit = defineEmits(['tipo-seleccionado'])

const tipos = [
  { key: TIPOS_TURNO.NORMAL, nombre: 'NORMAL' },
  { key: TIPOS_TURNO.PREFERENCIAL, nombre: 'PREFERENCIAL' },
]

const seleccionarTipo = (tipo) => {
  console.log('Emitting tipo-seleccionado:', tipo)
  emit('tipo-seleccionado', tipo)
}
</script>

<style scoped>
.seleccion-tipo {
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: stretch;
}

.tipos-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  flex: 1;
}

.tipo-btn {
  background: linear-gradient(135deg, #007bff 0%, #3399ff 100%);
  border: none;
  margin: 0;
  padding: 0;
  text-align: center;
  cursor: pointer;
  transition: transform 0.18s ease, filter 0.18s ease, box-shadow 0.18s ease;
  font-size: 11.2rem;
  font-weight: 700;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  border-right: 3px solid rgba(0,0,0,0.04);
  min-height: 200px;
  box-shadow: 0 6px 18px rgba(51,153,255,0.12);
}

.tipo-btn:last-child {
  border-right: none;
}

.tipo-btn:hover {
  transform: translateY(-3px);
  filter: brightness(0.95);
  box-shadow: 0 10px 24px rgba(51,153,255,0.18);
}

.tipo-nombre {
  display: block;
  font-size: 9.6rem;
  font-weight: bold;
}

.tipo-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.tipo-icon { font-size: 12.8rem; }
.tipo-icons-line { font-size: 13.6rem; display: inline-flex; gap: 8px; align-items: center; }
</style>
