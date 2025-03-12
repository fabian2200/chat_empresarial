<template>
  <div class="login-container">
    <div class="login-box">
      <div class="logo-container d-flex justify-content-center align-items-center">
        <img :src="baseUrl+'images/logo_empresa.png'" alt="Logo" class="logo">
      </div>
      <div class="text-center mb-4">
        <h2 class="login-title">Inicio de Sesión</h2>
        <p class="login-subtitle">Accede a tu chat empresarial</p>
      </div>
      
      <form @submit.prevent="handleSubmit" class="login-form">
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
        
        <div class="form-group d-flex justify-content-between align-items-center">
          <div class="form-check">
            <input 
              type="checkbox" 
              class="form-check-input" 
              id="remember" 
              v-model="form.remember"
            >
            <label class="form-check-label" for="remember">Recordarme</label>
          </div>
          <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
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
          {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>
      </form>

      <div class="text-center mt-4">
        <p class="register-link">
          ¿No tienes una cuenta? 
          <router-link to="/register" class="register-button">
            Regístrate
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { showSuccessMessage, showErrorMessage } from '../utils/swal';
import { authService } from '../services/api';
import { baseUrl } from '../baseUrl';

export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      },
      loading: false,
      error: null,
      showPassword: false,
      baseUrl: baseUrl
    }
  },
  methods: {
    async handleSubmit() {
      this.loading = true;
      this.error = null;

      try {
        const response = await authService.login(this.form.email, this.form.password);
        
        if (response.status === 'success') {
          await showSuccessMessage('¡Bienvenido!', response.message);
          setTimeout(() => {
            localStorage.setItem('user', JSON.stringify(response.user));
            this.$router.push('/principal/chat');
          }, 1200);
        }
      } catch (error) {
        console.log(error);
        const errorMessage = error.response.data.message || 'Error al iniciar sesión';
        await showErrorMessage('Error', errorMessage);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>

.login-box {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  padding: 40px;
  width: 100%;
  max-width: 450px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  position: relative;
  z-index: 2;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.4);
  transform: translateY(0);
  transition: transform 0.3s ease;
}

.login-box:hover {
  transform: translateY(-5px);
}

.logo {
  width: 160px;
  height: auto;
}

.login-title {
  color: #1a237e;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 10px;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.login-subtitle {
  color: #444;
  font-size: 16px;
  font-weight: 500;
}

.form-group {
  margin-bottom: 20px;
}

.custom-input {
  border-radius: 8px;
  padding: 12px 15px;
  border: 1px solid #ddd;
  transition: all 0.3s ease;
}

.custom-input:focus {
  border-color: #1a237e;
  box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
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
}

.password-toggle:hover {
  color: #1a237e;
}

.login-button {
  background: #1a237e;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.login-button:hover {
  background: #0d47a1;
  transform: translateY(-2px);
}

.login-button:disabled {
  background: #90caf9;
  cursor: not-allowed;
}

.forgot-password {
  color: #1a237e;
  text-decoration: none;
  font-size: 14px;
}

.forgot-password:hover {
  color: #0d47a1;
  text-decoration: underline;
}

.register-link {
  color: #666;
  margin: 0;
}

.register-button {
  color: #1a237e;
  text-decoration: none;
  font-weight: 600;
}

.register-button:hover {
  color: #0d47a1;
  text-decoration: underline;
}

.alert {
  border-radius: 8px;
  margin-bottom: 20px;
}

@media (max-width: 480px) {
  .login-box {
    padding: 30px 20px;
  }
  
  .login-title {
    font-size: 24px;
  }
}
</style> 