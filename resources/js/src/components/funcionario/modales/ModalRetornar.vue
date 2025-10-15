<template>
  <div class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">RETORNAR FICHA A ESPERA</h3>
        <button @click="$emit('cancelar')" class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label text-left">JUSTIFICATIVO</label>
          <textarea
            v-model="justificativo"
            class="form-control"
            rows="4"
            placeholder="Escriba el motivo por el cual retorna la ficha a espera"
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button @click="guardar" class="btn btn-guardar-gradient" :disabled="!justificativoValido">
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

const justificativo = ref('')

const justificativoValido = computed(() => justificativo.value.trim() !== '')

const guardar = () => {
  emit('guardar', justificativo.value.trim())
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.2);
  padding: var(--spacing-xl);
  min-width: 350px;
  max-width: 90vw;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-title {
  font-size: var(--font-size-lg);
  font-weight: bold;
}
.modal-close {
  background: none;
  border: none;
  font-size: var(--font-size-xl);
  cursor: pointer;
}
.form-group {
  margin-bottom: var(--spacing-lg);
}
.form-label {
  font-weight: 500;
  margin-bottom: var(--spacing-sm);
  display: block;
}
.form-control {
  width: 100%;
  padding: var(--spacing-sm);
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: var(--font-size-md);
}
.btn-guardar-gradient {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  border-radius: 8px;
  padding: var(--spacing-md) var(--spacing-xl);
  font-weight: bold;
  margin-right: var(--spacing-md);
}
.btn-cancelar-gradient {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border: none;
  border-radius: 8px;
  padding: var(--spacing-md) var(--spacing-xl);
  font-weight: bold;
}
</style>
