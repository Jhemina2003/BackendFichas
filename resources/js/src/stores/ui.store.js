import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUIStore = defineStore('ui', () => {
  const loading = ref(false)
  const modals = ref({
    observacion: false,
    redirigir: false,
  })

  const setLoading = (value) => {
    loading.value = value
  }

  const openModal = (modalName) => {
    modals.value[modalName] = true
  }

  const closeModal = (modalName) => {
    modals.value[modalName] = false
  }

  return {
    loading,
    modals,
    setLoading,
    openModal,
    closeModal,
  }
})
