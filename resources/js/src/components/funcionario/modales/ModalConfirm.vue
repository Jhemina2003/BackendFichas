<template>
  <div class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">{{ title }}</h3>
        <button @click="$emit('cancelar')" class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <p>{{ message }}</p>
      </div>
      <div class="modal-footer">
        <button @click="$emit('confirmar')" :class="['btn', confirmClass]">
          {{ confirmText }}
        </button>
        <button @click="$emit('cancelar')" class="btn btn-cancelar-gradient">
          {{ cancelText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  title: { type: String, default: 'Confirmar' },
  message: { type: String, default: '¿Estás seguro?' },
  confirmText: { type: String, default: 'CONFIRMAR' },
  cancelText: { type: String, default: 'CANCELAR' },
  confirmVariant: { type: String, default: '' }, // 'start' | 'close' or ''
})

const confirmClass = computed(() => {
  if (props.confirmVariant === 'start') return 'btn-iniciar'
  if (props.confirmVariant === 'close') return 'btn-cerrar'
  return 'btn-guardar-gradient'
})
</script>

<style scoped>
/* Reuse existing modal styles pattern used in other modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1000;
}
.modal {
  background: white;
  border-radius: var(--border-radius);
  width: 480px;
  max-width: calc(100% - 32px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
  overflow: hidden;
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-md);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}
.modal-title {
  margin: 0;
  font-size: var(--font-size-lg);
}
.modal-close {
  background: transparent;
  border: none;
  font-size: 1.6rem;
  cursor: pointer;
}
.modal-body {
  padding: var(--spacing-md);
}
.modal-footer {
  display: flex;
  gap: var(--spacing-sm);
  justify-content: flex-end;
  padding: var(--spacing-md);
}
.btn-guardar-gradient {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border: none;
  padding: 0.6rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
}
.btn-cancelar-gradient {
  background: #f0f0f0;
  color: black;
  border: none;
  padding: 0.6rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
}

/* Styles copied from Sidebar to ensure identical appearance */
.btn-iniciar {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  padding: 0.6rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
}
.btn-cerrar {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border: none;
  padding: 0.6rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
}
</style>
