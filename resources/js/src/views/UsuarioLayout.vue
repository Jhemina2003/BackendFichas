<template>
  <div class="usuario-layout">
    <Header />
    <div class="usuario-main">
      <MainActions />
    </div>
    <Footer />
  </div>
</template>

<script setup>
import Header from '../components/shared/Header.vue'
import Footer from '../components/shared/Footer.vue'
import MainActions from '../components/usuario/MainActions.vue'
</script>

<style scoped>
.usuario-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.usuario-main {
  flex: 1;
  background-color: white;
}
</style>
