<template>
  <div class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">OBSERVACIÓN</h3>
        <button @click="$emit('cancelar')" class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label text-left">OBSERVACIÓN SOBRE FICHA</label>
          <select v-model="observacionSeleccionada" class="form-select">
            <option value="" disabled>Seleccione una observación</option>
            <option v-for="obs in observaciones" :key="obs" :value="obs">
              {{ obs }}
            </option>
          </select>
        </div>
        <div class="form-group" v-if="observacionSeleccionada === 'Otro'">
          <label class="form-label text-left">OTRA OBSERVACIÓN *</label>
          <textarea
            v-model="observacionPersonalizada"
            class="form-control"
            rows="3"
            placeholder="Escriba su observación aquí"
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button @click="guardar" class="btn btn-guardar-gradient" :disabled="!observacionValida">
          GUARDAR
        </button>
        <button @click="$emit('cancelar')" class="btn btn-cancelar-gradient">CANCELAR</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { OBSERVACIONES_PREDEFINIDAS } from '../../../utils/constants'

const emit = defineEmits(['guardar', 'cancelar'])

const observaciones = OBSERVACIONES_PREDEFINIDAS
const observacionSeleccionada = ref('')
const observacionPersonalizada = ref('')

const observacionValida = computed(() => {
  if (observacionSeleccionada.value === 'Otro') {
    return observacionPersonalizada.value.trim() !== ''
  }
  return observacionSeleccionada.value !== ''
})

const guardar = () => {
  const observacionFinal =
    observacionSeleccionada.value === 'Otro'
      ? observacionPersonalizada.value
      : observacionSeleccionada.value

  emit('guardar', observacionFinal)
}
</script>

<style scoped>
.form-help {
  font-size: var(--font-size-sm);
  color: var(--color-secondary);
  margin-bottom: var(--spacing-sm);
  font-style: italic;
}
</style>
