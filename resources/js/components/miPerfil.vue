<template>
    <div>
        <form @submit.prevent="updateProfile">
            <div class="text-center mb-4 mt-4">
                <img 
                    :src="'/images/'+yo?.avatar+'.png'" 
                    class="rounded-circle mb-3"
                    width="100" 
                    height="100"
                    :alt="yo?.name"
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
                    class="form-control"
                    v-model="profileData.email"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Empresa:</label>
                <select class="form-select" v-model="profileData.empresa">
                    <option v-for="empresa in empresas" :key="empresa" :value="empresa">{{ empresa }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña:</label>
                <input 
                    type="password" 
                    class="form-control"
                    v-model="profileData.password"
                    placeholder="Dejar en blanco para mantener la actual"
                >
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

export default {
    name: 'miPerfil',
    data() {
        return {
            profileData: {
                name: '',
                email: '',
                empresa: '',
                telefono: '',
                password: ''
            },
            yo: null,
            empresas: [
                'LEER Ingeniería',
                'CSI Ingeniería',
                'GLIX Ingeniería',
                'RGL Ingeniería',
            ]
        }
    },
    mounted() {
        this.misDatos();
    },
    methods: {
        async misDatos() {
            this.yo = JSON.parse(localStorage.getItem('user'));

            var response = await perfilService.misDatos(this.yo.id);

            if (response.status == "success") {
                this.profileData = response.data;
            }
        },
        async updateProfile() {
            var response = await perfilService.actualizarPerfil(this.yo.id, this.profileData);
            if (response.status == "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Perfil actualizado',
                })
                await this.misDatos();
                localStorage.setItem('user', JSON.stringify(this.profileData));
                this.yo = JSON.parse(localStorage.getItem('user'));
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                })
            }
        }
    }
}
</script>
<style>
    
</style>