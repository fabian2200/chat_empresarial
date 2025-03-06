import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './bootstrap';

// Importar Font Awesome
import '@fortawesome/fontawesome-free/css/all.min.css';

// Importar Bootstrap y sus estilos
import 'bootstrap/dist/css/bootstrap.min.css';
import * as bootstrap from 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'sweetalert2/dist/sweetalert2.min.css';

const app = createApp(App);
app.use(router);
app.mount('#app');
