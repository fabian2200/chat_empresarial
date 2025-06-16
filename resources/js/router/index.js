import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import Chat from '../components/Chat.vue'
import Principal from '../components/Principal.vue'
import Grupos from '../components/Grupos.vue'
import { baseUrl } from '../baseUrl';
import soloChat from '../components/soloChat.vue';

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
  },
  {
    path: '/principal',
    name: 'Principal',
    component: Principal,
    children: [
      {
        path: 'chat',
        name: 'chat',
        component: Chat,
      },
      {
        path: 'grupos',
        name: 'grupos',
        component: Grupos,
      }
    ]
  },
  {
    path: '/chat-redireccionado-workboard/:id_mio?/:id_amigo?',
    name: 'chat-redireccionado-workboard',
    component: soloChat,
  },
]

const router = createRouter({
  history: createWebHistory(baseUrl),
  routes
})

export default router 