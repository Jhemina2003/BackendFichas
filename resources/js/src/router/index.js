import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'

const routes = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
  },
  {
    path: '/funcionario',
    name: 'Funcionario',
    component: () => import('../views/FuncionarioLayout.vue'),
    meta: { requiresAuth: true, role: 'funcionario' },
  },
  {
    path: '/usuario',
    name: 'Usuario',
    component: () => import('../views/UsuarioLayout.vue'),
    meta: { requiresAuth: true, role: 'usuario' },
  },
  // Nuevas rutas para ventanas emergentes - SIN autenticación
  {
    path: '/generar-ticket',
    name: 'GenerarTicketView',
    component: () => import('../views/GenerarTicketView.vue'),
  },
  {
    path: '/pantalla-publica',
    name: 'PantallaPublicaView',
    component: () => import('../views/PantallaPublicaView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // No requerir autenticación para las vistas públicas de tickets
  if (to.path === '/generar-ticket' || to.path === '/pantalla-publica') {
    next()
    return
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresAuth && to.meta.role && authStore.user?.role !== to.meta.role) {
    next(authStore.user?.role === 'funcionario' ? '/funcionario' : '/usuario')
  } else {
    next()
  }
})

export default router
