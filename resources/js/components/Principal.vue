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
                <select class="form-select" v-model="profileData.avatar">
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
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            activeMenu: 'chat',
            profileData: {
                password: '',
                empresa: '',
                avatar: ''
            },
            empresas: [
                'LEER Ingeniería',
                'CSI Ingeniería',
                'GLIX Ingeniería',
                'RGL Ingeniería',
            ],
            profileModal: null
        }
    },
    mounted() {
        this.verificarRuta();
        this.verificarPrimeraVez();
    },
    methods: {
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
            if(this.profileData.password === '' || this.profileData.empresa === '' || this.profileData.avatar === '') {
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