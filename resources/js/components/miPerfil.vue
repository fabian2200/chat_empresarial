<template>
    <div>
        <loading :active="isLoading" 
            :can-cancel="true"
            loader="bars" 
            color="#38b4c5"
            :height=100
            :width=200
            :is-full-page="true">
        </loading>
        <form @submit.prevent="updateProfile" autocomplete="off">
            <div class="text-center mb-3 mt-3" style="position: relative;">
                <img 
                    style="border: 2px solid #e7e7e7 !important; padding: 5px;"
                    :src="urlImagenAvatar" 
                    class="rounded-circle mb-3"
                    width="100" 
                    height="100"
                    :alt="yo?.name"
                >
                <div class="camera-icon-container">
                    <label for="avatarInput" class="camera-icon">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <input 
                    type="file" 
                    id="avatarInput" 
                    @change="onFileChange" 
                    style="display: none;"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input 
                    type="text" 
                    class="form-control"
                    v-model="profileData.name"
                    required
                >
            </div>


            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input 
                    type="email" 
                    autocomplete="off"
                    name="email2"
                    class="form-control"
                    v-model="profileData.email"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña:</label>
                <input 
                    type="password" 
                    autocomplete="off"
                    name="password2"
                    class="form-control"
                    v-model="profileData.password"
                    placeholder="Dejar en blanco para mantener la actual"
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
            

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    Actualizar Perfil
                </button>
            </div>
        </form>
    </div>
</template>
<script>
import { perfilService } from '../services/api'
import Swal from 'sweetalert2'
import { baseUrl } from '../baseUrl';
import Loading from 'vue3-loading-overlay';
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';

export default {
    components: {
        Loading
    },
    name: 'miPerfil',
    data() {
        return {
            profileData: {
                name: '',
                email: '',
                empresa: '',
                genero: '',
                avatar: '',
                password: ''
            },
            yo: null,
            empresas: [
                'LEER Ingeniería',
                'CSI Ingeniería',
                'GLIX Ingeniería',
                'RGL Ingeniería',
            ],
            baseUrl: baseUrl,
            urlImagenAvatar: '',
            fileAvatar: null,
            isLoading: false
        }
    },
    async mounted() {
        await this.verificarLogin();
        if (!this.verificarLogin()) {
            this.$router.push('/login');
        }else {
            this.misDatos();
        }
    },
    methods: {
        verificarLogin() {
            const user = localStorage.getItem('user');
            if (!user) {
                return false;
            }
            return true;
        },
        async misDatos() {
            this.yo = JSON.parse(localStorage.getItem('user'));

            var response = await perfilService.misDatos(this.yo.id);

            if (response.status == "success") {
                this.profileData = response.data;
                this.urlImagenAvatar = this.baseUrl+'images/'+this.profileData.avatar;
            }
        },
        async updateProfile() {
            this.isLoading = true;
            var formData = new FormData();
            formData.append('id', this.yo.id);
            formData.append('name', this.profileData.name);
            formData.append('email', this.profileData.email);
            formData.append('empresa', this.profileData.empresa);
            formData.append('genero', this.profileData.genero);
            formData.append('avatar', this.profileData.avatar);

            if (this.profileData.password) {
                formData.append('password', this.profileData.password);
            }

            if (this.fileAvatar) {
                formData.append('avatar_file', this.fileAvatar);
            }

            var response = await perfilService.actualizarPerfil(this.yo.id, formData);
            if (response.status == "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Perfil actualizado',
                    showConfirmButton: false,
                    timer: 1500
                })
                await this.misDatos();
                localStorage.setItem('user', JSON.stringify(this.profileData));
                this.yo = JSON.parse(localStorage.getItem('user'));
                this.fileAvatar = null;
                this.isLoading = false;
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                })
                this.isLoading = false;
            }
        },
        onFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.urlImagenAvatar = e.target.result;
                };
                reader.readAsDataURL(file);
                this.fileAvatar = file;
            }else{
                this.fileAvatar = null;
                this.urlImagenAvatar = this.baseUrl+'images/'+this.profileData.avatar;
            }
        },
    }
}
</script>
<style>
    .camera-icon-container {
        position: absolute;
        bottom: 16%;
        font-size: 17px;
        background-color: #5095fb;
        color: white;
        width: 9.5%;
        height: 38.5%;
        padding: 10px;
        border-radius: 50%;
        cursor: pointer;
        left: 54%;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 3px solid white;
        cursor: pointer !important;
    }

    .camera-icon-container label {
        cursor: pointer !important;
    }
</style>