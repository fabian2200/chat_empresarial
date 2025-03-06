<template>
  <div class="login-container">
    <div class="login-box register-box">
      <div class="text-center mb-4">
        <h2 class="login-title">Registro de Usuario</h2>
      </div>
      
      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="form-group">
          <label for="name" class="form-label">
            <i class="fas fa-user me-2"></i>Nombre Completo
          </label>
          <input 
            type="text" 
            class="form-control custom-input" 
            id="name" 
            v-model="form.name" 
            placeholder="Tu nombre completo"
            required
          >
        </div>

        <div class="form-group">
          <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>Correo Electrónico
          </label>
          <input 
            type="email" 
            class="form-control custom-input" 
            id="email" 
            v-model="form.email" 
            placeholder="tu@empresa.com"
            required
          >
        </div>

        <div class="form-group">
          <label for="empresa" class="form-label">
            <i class="fas fa-building me-2"></i>Empresa
          </label>
          <select 
            class="form-control custom-input" 
            id="empresa" 
            v-model="form.empresa" 
            required
          >
            <option value="" disabled selected>Selecciona una empresa</option>
            <option v-for="empresa in empresas" :key="empresa" :value="empresa">
              {{ empresa }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="gender" class="form-label">
            <i class="fas fa-venus-mars me-2"></i>Género
          </label>
          <select 
            class="form-control custom-input" 
            id="gender" 
            v-model="form.gender" 
            required
          >
            <option value="" disabled selected>Selecciona tu género</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
            <option value="otro">Otro</option>
          </select>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>Contraseña
          </label>
          <div class="password-input">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              class="form-control custom-input" 
              id="password" 
              v-model="form.password" 
              placeholder="••••••••"
              required
            >
            <i 
              :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'password-toggle']"
              @click="showPassword = !showPassword"
            ></i>
          </div>
        </div>

        <div v-if="error" class="alert alert-danger">
          {{ error }}
        </div>

        <button 
          type="submit" 
          class="btn btn-primary w-100 login-button"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
          {{ loading ? 'Registrando...' : 'Registrarse' }}
        </button>
      </form>

      <div class="text-center mt-4">
        <p class="register-link">
          ¿Ya tienes una cuenta? 
          <router-link to="/login" class="register-button">
            Inicia Sesión
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { showSuccessMessage, showErrorMessage } from '../utils/swal';

export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        empresa: '',
        gender: '',
        password: '',
        confirmPassword: ''
      },
      empresas: [
        'LEER Ingeniería',
        'CSI Ingeniería',
        'GLIX Ingeniería',
        'RGL Ingeniería',
      ],
      loading: false,
      error: null,
      showPassword: false
    }
  },
  methods: {
    async handleSubmit() {
  
      this.loading = true;
      this.error = null;

      try {
        const formData = new FormData();
        formData.append('name', this.form.name);
        formData.append('email', this.form.email);
        formData.append('empresa', this.form.empresa);
        formData.append('avatar', this.form.gender);
        formData.append('password', this.form.password);

        const response = await axios.post('/api/register', formData);

        if (response.data.status === 'success') {
          await showSuccessMessage('¡Registro Exitoso!', response.data.message);
          this.$router.push('/login');
        }
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Error al registrar usuario';
        await showErrorMessage('Error', errorMessage);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(rgba(26, 34, 126, 0.452), rgba(13, 72, 161, 0.226)),
              url('/images/photo-1497366754035-f200968a6e72.avif') no-repeat center center;
  background-size: cover;
  background-attachment: fixed;
  padding: 20px;
}

.register-box {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 20px;
  padding: 30px 40px;
  width: 100%;
  max-width: 800px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  position: relative;
  z-index: 2;
}

.login-title {
  color: #1a237e;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 10px;
}

.login-subtitle {
  color: #666;
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 25px;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  color: #333;
  font-weight: 600;
  margin-bottom: 8px;
  display: block;
}

.form-label i {
  color: #1a237e;
}

.custom-input {
  height: 45px;
  border-radius: 12px;
  padding: 10px 15px;
  border: 2px solid #e0e0e0;
  font-size: 15px;
  transition: all 0.3s ease;
  background-color: #f8f9fa;
}

.custom-input:focus {
  border-color: #1a237e;
  background-color: #fff;
  box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.15);
}

select.custom-input {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 2.5rem;
}

select.custom-input:focus {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%231a237e' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
}

.gender-options,
.avatar-options,
.avatar-option,
.avatar-icon,
.avatar-img {
  display: none;
}

.password-input {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #666;
  font-size: 18px;
  padding: 5px;
}

.login-button {
  height: 45px;
  margin-top: 5px;
}

.login-button:disabled {
  background: #90caf9;
  transform: none;
  box-shadow: none;
}

.register-link {
  margin-top: 25px;
  color: #666;
  font-size: 15px;
}

.register-button {
  color: #1a237e;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.register-button:hover {
  color: #283593;
  text-decoration: underline;
}

.alert {
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 20px;
  border: none;
  background-color: #ffebee;
  color: #c62828;
}

@media (max-width: 768px) {
  .register-box {
    max-width: 95%;
    padding: 25px;
    margin: 10px;
  }
  
  .avatar-options {
    gap: 15px;
  }
  
  .avatar-icon {
    width: 60px;
    height: 60px;
  }
  
  .avatar-icon i {
    font-size: 30px;
  }
}

@media (max-width: 480px) {
  .register-box {
    padding: 20px;
  }
  
  .avatar-options {
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  
  .avatar-option {
    padding: 10px;
  }
  
  .avatar-icon {
    width: 50px;
    height: 50px;
  }
  
  .avatar-icon i {
    font-size: 25px;
  }
}
</style> 