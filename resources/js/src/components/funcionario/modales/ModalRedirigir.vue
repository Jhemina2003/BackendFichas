<template>
  <div class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">REDIRIGIR</h3>
        <button @click="$emit('cancelar')" class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label text-left">REDIRIGIR FICHA</label>
          <textarea
            v-model="motivo"
            class="form-control"
            rows="4"
            placeholder="Escriba el motivo de la redirección"
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button @click="guardar" class="btn btn-guardar-gradient" :disabled="!motivoValido">
          GUARDAR
        </button>
        <button @click="$emit('cancelar')" class="btn btn-cancelar-gradient">CANCELAR</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const emit = defineEmits(['guardar', 'cancelar'])

const motivo = ref('')

const motivoValido = computed(() => motivo.value.trim() !== '')

const guardar = () => {
  emit('guardar', motivo.value.trim())
}
</script>
