import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import Chat from '../components/Chat.vue'
import Principal from '../components/Principal.vue'
import Grupos from '../components/Grupos.vue'
import { baseUrl } from '../baseUrl';

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false }
  },
  {
    path: '/principal',
    name: 'Principal',
    component: Principal,
    children: [
      {
        path: 'chat',
        name: 'chat',
        component: Chat
      },
      {
        path: 'grupos',
        name: 'grupos',
        component: Grupos
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(baseUrl),
  routes
})

// Guardia de navegación para proteger rutas
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  
  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if ((to.path === '/login' || to.path === '/register') && token) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router 