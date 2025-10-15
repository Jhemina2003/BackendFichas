<template>
  <div class="funcionario-layout">
    <Header />
    <div class="funcionario-main">
      <SidebarFuncionario />
      <div class="funcionario-content">
        <DashboardFuncionario />
      </div>
    </div>
    <Footer />
  </div>
</template>

<script setup>
import Header from '../components/shared/Header.vue'
import Footer from '../components/shared/Footer.vue'
import SidebarFuncionario from '../components/funcionario/SidebarFuncionario.vue'
import DashboardFuncionario from '../components/funcionario/DashboardFuncionario.vue'
</script>

<style scoped>
.funcionario-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.funcionario-main {
  flex: 1;
  display: flex;
  min-height: 0;
  height: 100%;
}

.funcionario-content {
  flex: 1;
  background-color: white; /* asegurar fondo blanco en el content del funcionario */
  color: #111; /* texto oscuro para buen contraste */
  /* Hacer que el content ocupe todo el alto disponible y oculte el scroll del layout;
     los componentes internos deberán manejar su propio overflow si es necesario */
  min-height: 0; /* importante para que children con height:100% funcionen en flex */
  overflow: hidden;
}
</style>
