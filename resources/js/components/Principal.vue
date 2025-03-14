<template>
    <div>
        <div class="app-container d-flex">
            <div class="menu-container">
                <div class="menu-item d-flex flex-column">
                    <router-link class="menu-item-link" :class="{ 'active': activeMenu === 'chat' }" @click="setActiveMenu('chat')" to="/principal/chat"><i class="fas fa-comments"></i> Chats</router-link>
                    <router-link class="menu-item-link" :class="{ 'active': activeMenu === 'grupos' }" @click="setActiveMenu('grupos')" to="/principal/grupos"><i class="fas fa-users"></i> Grupos</router-link>
                </div>
            </div>
            <router-view></router-view>
        </div>
    </div>

    <div 
      class="modal fade" 
      id="profileModal" 
      tabindex="-1" 
      ref="profileModal"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Actualizar datos</h5>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Contraseña:</label>
                <input 
                    type="password" 
                    class="form-control"
                    v-model="profileData.password"
                    placeholder="Ingrese una nueva contraseña"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Empresa:</label>
                <select class="form-select" v-model="profileData.empresa">
                    <option v-for="empresa in empresas" :key="empresa" :value="empresa">{{ empresa }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Género:</label>
                <select class="form-select" v-model="profileData.genero">
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" @click="updateProfile">Actualizar datos <i class="fas fa-check"></i></button>
          </div>
        </div>
      </div>
    </div>

</template>
<script>
import * as bootstrap from 'bootstrap';
import { perfilService } from '../services/api';
import { userService } from '../services/api';
import Swal from 'sweetalert2';
import { baseUrl } from '../baseUrl';

export default {
    data() {
        return {
            activeMenu: 'chat',
            profileData: {
                password: '',
                empresa: '',
                genero: ''
            },
            empresas: [
                'LEER Ingeniería',
                'CSI Ingeniería',
                'GLIX Ingeniería',
                'RGL Ingeniería',
            ],
            profileModal: null,
            numero_mensajes_recibidos_chat: 0,
            numero_mensajes_recibidos_grupo: 0,
            usuario: null,
            updateInterval: null,
            baseUrl: baseUrl
        }
    },
    async mounted() {
        await this.verificarLogin();
        if (!this.verificarLogin()) {
            this.$router.push('/login');
        }else {
            this.verificarRuta();
            this.verificarPrimeraVez();
            this.updateInterval = setInterval(() => {
                this.consultarNumeroMensajes();
            }, 5000);
        }
    },
    methods: {
        async consultarNumeroMensajes() {
            const response = await userService.obtenerNumeroMensajesRecibidos(this.usuario.id);
            if(response.status === 'success') {
                var m_r = response.numero_mensajes_recibidos_chat;
                if(m_r > 0) {
                    this.notificarMensajesChat(m_r);
                }

                var m_r_grupo = response.numero_mensajes_recibidos_grupo;
                if(m_r_grupo > 0) {
                    this.notificarMensajesGrupo(m_r_grupo);
                }
            }
        },
        notificarMensajesChat(m_r) {
            if(this.numero_mensajes_recibidos_chat != 0) {
                if(this.numero_mensajes_recibidos_chat != m_r) {
                this.numero_mensajes_recibidos_chat = m_r;
                console.log("nuevo mensaje, ahora con: "+ this.numero_mensajes_recibidos_chat);
                var audio = new Audio(this.baseUrl+'sounds/sound.mp3');
                audio.play();
                }else{
                console.log("sigue siendo el mismo: "+ this.numero_mensajes_recibidos_chat);
                }
            }else{
                this.numero_mensajes_recibidos_chat = m_r;
                console.log("entro inicialmente con: "+ this.numero_mensajes_recibidos_chat);
            }
        },
        notificarMensajesGrupo(m_r) {
            if(this.numero_mensajes_recibidos_grupo != 0) {
                if(this.numero_mensajes_recibidos_grupo != m_r) {
                this.numero_mensajes_recibidos_grupo = m_r;
                console.log("nuevo mensaje grupo, ahora con: "+ this.numero_mensajes_recibidos_grupo);
                var audio = new Audio(this.baseUrl+'sounds/sound_2.mp3');
                audio.play();
                }else{
                console.log("sigue siendo el mismo grupo: "+ this.numero_mensajes_recibidos_grupo);
                }
            }else{
                this.numero_mensajes_recibidos_grupo = m_r;
                console.log("entro grupo inicialmente con: "+ this.numero_mensajes_recibidos_grupo);
            }
        },
        verificarLogin() {
            this.usuario = JSON.parse(localStorage.getItem('user'));
            if (!this.usuario) {
                return false;
            }
            return true;
        },
        verificarPrimeraVez() {
            this.usuario = JSON.parse(localStorage.getItem('user'));
            if(this.usuario.primera_vez === 0) {
                if(!this.profileModal) {
                    this.profileModal = new bootstrap.Modal(this.$refs.profileModal, {
                        backdrop: 'static',
                        keyboard: false
                    });
                }
                this.profileModal.show();
            }
        },
        updateProfile() {
            if(this.profileData.password === '' || this.profileData.empresa === '' || this.profileData.genero === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Todos los campos son requeridos',
                    showConfirmButton: false,
                    timer: 1500
                });
            }else{
                perfilService.actualizarDatos(this.usuario.id, this.profileData).then(response => {
                    if(response.status === 'success') {
                        this.profileModal.hide();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: 'Datos actualizados correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        this.misDatos();
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            }
        },
        async misDatos() {
            const response = await perfilService.misDatos(this.usuario.id);
            if(response.status === 'success') {
                this.usuario = response.data;
                localStorage.setItem('user', JSON.stringify(response.data));
            }
        },
        setActiveMenu(menu) {
            this.activeMenu = menu;
        },
        verificarRuta() {
            if(this.$route.path === '/principal/chat') {
                this.activeMenu = 'chat';
            }else if(this.$route.path === '/principal/grupos') {
                this.activeMenu = 'grupos';
            }
        }
    }
}
</script>
<style>
.app-container {
    padding: 20px;
}

.app-container::after {
    position: fixed;
    top: 0px;
    left: 0px;
    width: 100%;
    height: 127px;
    content: "";
    background-color: #061224;
    z-index: -100;
}

.app-container::before {
    position: fixed;
    top: 127px;
    left: 0px;
    width: 100%;
    height: calc(100vh - 127px);
    content: "";
    background-color: #dfdfdf;
    z-index: -99;
}

.menu-container {
    width: 88px;
    height: calc(100vh - 40px);
    background-color: #919294;
    color: #fff;
}

.menu-item-link {
    cursor: pointer;
    transition: all 0.3s ease;
    color: #fff;
    padding: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    margin: 10px;
    width: 60px;
    height: 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 12px;
}

.menu-item-link:hover {
    background-color: #b9b9b9;
}

.menu-item-link.active {
    background-color: #fff;
    color: #000;
}

</style>