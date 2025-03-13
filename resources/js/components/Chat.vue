<template>
  <div class="container-fluid h-100">
    <loading :active="isLoading" 
        :can-cancel="true"
        loader="bars" 
        color="#38b4c5"
        :height=100
        :width=200
        :on-cancel="onCancel"
        :is-full-page="true">
    </loading>
    <div class="row contenido_chat" style="overflow-y: hidden;">
      <!-- Lista de contactos (Sidebar) -->
      <div class="col-4 col-md-3 bg-light p-0" style="background-color: #fdfdfd !important;">
        <!-- Barra de búsqueda y nuevo chat -->
        <div class="p-3 border-bottom">
          <div class="d-flex gap-2 mb-3 justify-content-end">
            <div class="d-flex flex-column w-100">
              <h5 class="mb-0">Bienvenido</h5>
              <small class="text-muted">{{ yo?.name }}</small>
            </div>
            <button data-bs-toggle="tooltip" data-bs-placement="bottom" title="Nuevo Chat" class="btn btn-outline-success rounded-circle" @click="openNewChat">
              <i class="fa-solid fa-comment-medical"></i>
            </button>
            <button data-bs-toggle="tooltip" data-bs-placement="bottom" title="Difusión" class="btn btn-outline-primary rounded-circle" @click="openBroadcastModal">
              <i class="bi bi-broadcast"></i>
            </button>
            <button data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuración" class="btn btn-outline-secondary rounded-circle" @click="openProfile">
              <i class="bi bi-gear-fill"></i>
            </button>
            <button data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cerrar Sesión" class="btn btn-outline-danger rounded-circle" @click="logout">
              <i class="bi bi-box-arrow-right"></i>
            </button>
          </div>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <i class="bi bi-search"></i>
            </span>
            <input 
              type="text" 
              class="form-control border-start-0" 
              placeholder="Buscar contactos..."
              v-model="searchQuery"
              @keyup="buscarContactosChats"
            >
          </div>
        </div>

        <!-- Lista de contactos -->
        <div class="contacts-list overflow-auto" style="height: calc(100vh - 180px);">
          <div 
            v-for="chat in chatsFiltrados" 
            :key="chat.id"
            class="contact-item p-2  cursor-pointer"
            :class="{'active': selectedChat?.id === chat.id}"
            @click="seleccionarChat(chat)"
          >
            <div class="d-flex align-items-center elemento">
              <div class="position-relative">
                <img 
                  :src="baseUrl+'images/'+chat.avatar" 
                  class="rounded-circle"
                  width="50" 
                  height="50"
                  :alt="chat.nombre"
                >
                <span 
                  v-if="chat.online"
                  class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white"
                  style="width: 12px; height: 12px;"
                ></span>
              </div>
              <div class="ms-3 flex-grow-1" style="position: relative;">
                <small v-if="chat.mensajes_sin_leer > 0" class="badge bg-danger ms-2 numero_mensajes">
                  <i style="margin-right: 3px;" class="bi bi-chat-dots"></i> {{ chat.mensajes_sin_leer }}
                </small>
                <small class="text-muted">{{ chat.empresa }}</small>
                <br>
                <small class="mb-0">
                  {{ chat.nombre }} 
                  <span :class="chat.online ? 'badge bg-success text-black' : 'badge bg-warning text-black'">
                    {{ chat.online ? 'En línea' : 'Activo ' + chat.last_seen }}
                  </span>
                </small>
                <br>
                <small class="text-muted" style="font-size: 10px;">
                  Creado el {{ chat.fecha_chat }} a las {{ chat.hora_chat }} 
                </small>
              </div>
              <small class="text-muted">{{ chat.lastMessageTime }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Área del chat -->
      <div class="col-8 col-md-9 p-0 h-100">
        <template v-if="selectedChat">
          <!-- Encabezado del chat -->
          <div class="chat-header p-3 border-bottom bg-white" style="background-color: #e1e1e1 !important;">
            <div class="d-flex align-items-center">
              <img 
                :src="baseUrl+'images/'+selectedChat.avatar" 
                class="rounded-circle"
                width="45" 
                height="45"
                :alt="selectedChat.nombre"
              >
              <div class="ms-3">
                <h6 class="mb-0">{{ selectedChat.nombre }} <span :class="selectedChat.online ? 'badge bg-success' : 'badge bg-primary'">{{ selectedChat.online ? 'En línea' : 'Activo ' + selectedChat.last_seen }}</span></h6>
                <small class="text-muted">
                  {{ selectedChat.empresa }}
                </small>
              </div>
              <div class="ms-auto">
                <button class="btn btn-warning me-2" @click="cerrarChat">
                  <i class="bi bi-x-lg text-light"></i> Cerrar Chat
                </button>
              </div>
            </div>
          </div>

          <!-- Área de mensajes -->
          <div 
            class="chat-messages p-3 overflow-auto"
            style="height: calc(100vh - 186px);"
            ref="messageContainer"
          >
            <div 
              v-for="message in messages" 
              :key="message.id"
              class="mb-3"
              :class="{'text-end': message.is_mine}"
            >
              <div 
                class="message d-inline-block p-3"
                :class="[
                  message.is_mine ? 'message-mine bg-primary text-white' : 'message-other bg-white',
                  {'rounded-bottom-end-0': message.is_mine},
                  {'rounded-bottom-start-0': !message.is_mine}
                ]"
                style="max-width: 75%; position: relative;"
              >
                <div v-if="message.is_mine" style="position: absolute; right: -27px; top: 0px; z-index: 99;">
                    <img :src="baseUrl+'images/mine.png'" style="width: 30px; height: 50px;">
                </div>
                <div v-else style="position: absolute; left: -27px; top: 0px; z-index: 99;">
                    <img :src="baseUrl+'images/other.png'" style="width: 30px; height: 50px;">
                </div>
                <p :style="message.is_mine ? 'color: white;' : 'color: black;'" class="mb-1">{{ message.mensaje }}</p>
                
                <!-- Modificar vista previa del archivo -->
                <div v-if="message.tiene_archivo === 1" class="file-preview mb-2">
                  <div 
                    class="d-flex align-items-center p-2 rounded cursor-pointer" 
                    :class="message.is_mine ? 'bg-white bg-opacity-25' : 'bg-light'"
                    @click="openFileModal(message.mensaje)"
                  >
                    <i class="bi bi-file-earmark me-2" style="font-size: 1.5rem;"></i>
                    <div class="file-info">
                      <div :class="message.is_mine ? 'text-white' : 'text-dark'">{{ message.archivo }}</div>
                      <small :class="message.is_mine ? 'text-white-50' : 'text-muted'">Clic para ver</small>
                    </div>
                  </div>
                </div>

                <small v-if="message.tiene_archivo === 1" :class="message.is_mine ? 'text-white-50 peso_derecha' : 'text-muted peso_izquierda'">Peso: {{ message.peso }} <br></small>

                <small :class="message.is_mine ? 'text-white-50' : 'text-muted'" style="font-size: 10px;">
                  {{ message.fecha }} A las {{ message.hora }}
                  <span v-if="message.is_mine">
                    <i class="bi" :class="message.estado === 1 ? 'bi-check-all' : 'bi-check'"></i>
                    {{ message.estado === 1 ? 'Visto' : 'Entregado' }}
                  </span>
                </small>
              </div>
            </div>
          </div>

          <!-- Área de entrada de mensaje -->
          <div class="chat-input p-3" style="background-color: #f1ede6 !important;">
            <div class="input-group">
              <button style="padding: 0px !important; border-color: #f1ede6 !important; background-color: #f1ede6 !important;" class="btn btn-light" @click="openFileExplorer">
                <i class="bi bi-plus-lg" style="color: grey; font-size: 1.5rem;"></i>
              </button>
              <input
                style="border: 1px solid #c3c3c3;"
                type="text" 
                class="form-control mx-2" 
                placeholder="Escribe un mensaje..."
                v-model="newMessage"
                @keyup.enter="guardarMensaje('texto')"
              >
              <button 
                class="btn btn-primary rounded-circle"
                @click="guardarMensaje('texto')"
              >
                <i class="bi bi-send-fill"></i>
              </button>
            </div>
          </div>
        </template>

        <!-- Mensaje cuando no hay chat seleccionado -->
        <div 
          v-else  
          style="height:100vh !important;"
          class="h-100 d-flex align-items-center justify-content-center bg-light"
        >
          <div class="text-center text-muted">
            <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Selecciona un chat para comenzar</h5>
          </div>
        </div>
      </div>
    </div>

    <!-- Input file oculto para el explorador de archivos -->
    <input 
      type="file" 
      ref="fileInput" 
      class="d-none" 
      @change="handleFileSelected" 
      multiple
    >

    <!-- Modal de nuevo chat -->
    <div 
      class="modal fade" 
      id="newChatModal" 
      tabindex="-1" 
      ref="newChatModal"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nuevo Chat</h5>
            <button 
              type="button" 
              class="btn-close" 
              @click="closeNewChatModal"
            ></button>
          </div>
          <div class="modal-body">
            <!-- Barra de búsqueda -->
            <div class="input-group mb-3">
              <span class="input-group-text bg-white">
                <i class="bi bi-search"></i>
              </span>
              <input 
                type="text" 
                class="form-control" 
                placeholder="Buscar usuarios..."
                v-model="newChatSearchQuery"
              >
            </div>

            <!-- Lista de usuarios -->
            <div class="user-list" style="max-height: 300px; overflow-y: auto;">
              <div 
                v-for="user in availableUsers" 
                :key="user.id"
                class="d-flex align-items-center p-2 rounded cursor-pointer user-item"
                @click="createNewChat(user)"
              >
                <img 
                  :src="baseUrl+'images/'+user.avatar" 
                  class="rounded-circle"
                  width="40" 
                  height="40"
                  :alt="user.name"
                >
                <div class="ms-3">
                  <h6 class="mb-0">{{ user.name }} <span :class="user.online ? 'badge bg-success text-black' : 'badge bg-warning text-black'">{{ user.online ? 'En línea' : 'Activo ' + user.lastSeen }}</span></h6>
                  <small class="text-muted">
                    {{ user.empresa }}
                  </small>
                  <br>
                
                </div>
                <div class="ms-auto">
                  <i class="bi bi-plus-circle text-primary"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de perfil -->
    <div 
      class="modal fade" 
      id="profileModal" 
      tabindex="-1" 
      ref="profileModal"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Mi Perfil</h5>
            <button 
              type="button" 
              class="btn-close" 
              @click="closeProfileModal"
            ></button>
          </div>
          <div class="modal-body">
            <miPerfil ref="miPerfil" />
          </div>
        </div>
      </div>
    </div>

    <!-- Agregar el modal de archivo al final del template, antes del cierre de div container-fluid -->
    <div 
      class="modal fade" 
      id="fileModal" 
      tabindex="-1" 
      ref="fileModal"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Vista previa del archivo</h5>
            <button 
              type="button" 
              class="btn-close" 
              @click="closeFileModal"
            ></button>
          </div>
          <div class="modal-body text-center">
            <!-- Vista previa según el tipo de archivo -->
            <template v-if="selectedFile">
              <!-- Imágenes -->
              <img 
                v-if="isImage(selectedFile)"
                :src="baseUrl+'archivos/' + selectedFile" 
                class="img-fluid mb-3"
                alt="Vista previa"
              >
              <!-- PDFs -->
              <iframe
                v-else-if="isPDF(selectedFile)"
                :src="baseUrl+'archivos/' + selectedFile"
                width="100%"
                height="500px"
                class="mb-3"
              ></iframe>
              <!-- Otros archivos -->
              <div v-else class="alert alert-info">
                Este tipo de archivo no tiene vista previa disponible
              </div>
            </template>
          </div>
          <div class="modal-footer">
            <a 
              :href="baseUrl+'archivos/' + selectedFile" 
              class="btn btn-primary"
              download
            >
              <i class="bi bi-download me-2"></i>Descargar
            </a>
            <button 
              type="button" 
              class="btn btn-secondary" 
              @click="closeFileModal"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de difusión -->
    <div 
      class="modal fade" 
      id="broadcastModal" 
      tabindex="-1" 
      ref="broadcastModal"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Mensaje de Difusión</h5>
            <button 
              type="button" 
              class="btn-close" 
              @click="closeBroadcastModal"
            ></button>
          </div>
          <div class="modal-body">
            <!-- Barra de búsqueda -->
            <div class="input-group mb-3">
              <span class="input-group-text bg-white">
                <i class="bi bi-search"></i>
              </span>
              <input 
                type="text" 
                class="form-control" 
                placeholder="Buscar usuarios..."
                v-model="broadcastSearchQuery"
                @keyup="searchUsersDifusion"
              >
            </div>

            <!-- Lista de usuarios seleccionables -->
            <div class="user-list mb-3" style="max-height: 200px; overflow-y: auto;">
              <div 
                v-for="user in availableUsersDifusion" 
                :key="user.id"
                class="d-flex align-items-center p-2 rounded cursor-pointer user-item"
              >
                <div class="form-check">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :value="user.id"
                    v-model="selectedUsers"
                  >
                </div>
                <img 
                  :src="baseUrl+'images/'+user.avatar" 
                  class="rounded-circle ms-2"
                  width="40" 
                  height="40"
                  :alt="user.name"
                >
                <div class="ms-3">
                  <h6 class="mb-0">{{ user.name }}</h6>
                  <small class="text-muted">{{ user.empresa }}</small>
                </div>
              </div>
            </div>

            <!-- Área de mensaje -->
            <div class="mb-3">
              <textarea 
                class="form-control" 
                v-model="broadcastMessage" 
                rows="3" 
                placeholder="Escribe tu mensaje..."
              ></textarea>
            </div>

            <!-- Área de archivo -->
            <div class="mb-3">
              <button class="btn btn-outline-secondary" @click="openBroadcastFileExplorer">
                <i class="bi bi-paperclip"></i> Adjuntar archivo
              </button>
              <span v-if="broadcastFile" class="ms-2">
                {{ broadcastFile.name }}
                <button class="btn btn-sm btn-link text-danger" @click="removeBroadcastFile">
                  <i class="bi bi-x"></i>
                </button>
              </span>
            </div>
          </div>
          <div class="modal-footer">
            <button 
              type="button" 
              class="btn btn-danger" 
              @click="closeBroadcastModal"
            >
              Cancelar <i class="bi bi-x"></i>
            </button>
            <button 
              type="button" 
              class="btn btn-success" 
              @click="sendBroadcastMessage"
              :disabled="!selectedUsers.length || !broadcastMessage"
            >
              Enviar mensaje <i class="bi bi-send"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Input file oculto para archivos de difusión -->
    <input 
      type="file" 
      ref="broadcastFileInput" 
      class="d-none" 
      @change="handleBroadcastFileSelected"
    >
  </div>
</template>

<script>
import * as bootstrap from 'bootstrap'
import { userService, chatService } from '../services/api' 
import Swal from 'sweetalert2'
import miPerfil from './miPerfil.vue'
import { baseUrl } from '../baseUrl';
import Loading from 'vue3-loading-overlay';
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';

export default {
  components: {
    miPerfil,
    Loading
  },
  name: 'Chat',
  data() {
    return {
      searchQuery: '',
      selectedChat: null,
      newMessage: '',
      chats: [],
      chatsFiltrados: [],
      messages: [
       
      ],
      newChatSearchQuery: '',
      users: [],
      availableUsers: [],
      modal: null,
      yo: null,
      profileModal: null,
      profileData: {
        name: '',
        email: '',
        empresa: '',
        telefono: '',
        password: '',
        avatar: ''
      },
      empresas: [
        'LEER Ingeniería',
        'CSI Ingeniería',
        'GLIX Ingeniería',
        'RGL Ingeniería',
      ],
      archivo: null,
      selectedFile: null,
      fileModal: null,
      updateInterval: null,
      broadcastModal: null,
      broadcastMessage: '',
      selectedUsers: [],
      broadcastFile: null,
      broadcastSearchQuery: '',
      availableUsersDifusion: [],
      baseUrl: baseUrl,
      isLoading: false
    }
  },
  methods: {
    async seleccionarMiUsuario() {
      this.yo = JSON.parse(localStorage.getItem('user'));
      return this.yo;
    },
    verificarChatActual() {
      const id_mio = localStorage.getItem('id_mio');
      const id_chat = localStorage.getItem('id_chat');
      if (id_mio && id_chat) {
        const chat = this.chats.find(chat => chat.id == id_chat);
        this.seleccionarChat(chat);
      }
    },
    async logout() {
      Swal.fire({   
        icon: 'warning',
        title: '¿Estás seguro?',
        text: '¿Estás seguro de querer cerrar sesión?',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          try {
            userService.logout(this.yo.id);
          } catch (error) {
            console.error('Error al cerrar sesión:', error);
          }
        }
      });
    },
    async loadUsers() {
      try {
        this.users = await userService.getUsers();
        this.users = this.users.filter(user => user.id != this.yo.id);
        this.availableUsers = this.users.filter(user => user.id != this.yo.id);
        this.availableUsersDifusion = this.users.filter(user => user.id != this.yo.id);
      } catch (error) {
        console.error('Error al cargar usuarios:', error);
      }
    },
    async loadChats() {
      try {
        const chats = await chatService.obtenerChatsUsuario(this.yo.id);
        this.chats = chats.chats;
        this.chatsFiltrados = chats.chats;
      } catch (error) {
        console.error('Error al cargar chats:', error);
      }
    },
    async seleccionarChat(chat) {
      this.selectedChat = chat;
      localStorage.setItem('id_chat', chat.id);
      localStorage.setItem('id_mio', this.yo.id);
      
      await this.loadMessages(chat.id);
      await this.loadChats();

      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },
    async loadMessages() {
      const id_mio = localStorage.getItem('id_mio');
      const id_chat = localStorage.getItem('id_chat');
      const messages = await chatService.obtenerMensajesChat(id_mio, id_chat);
      this.messages = messages.mensajes;
    },  
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messageContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
    openNewChat() {
      if (!this.modal) {
        this.modal = new bootstrap.Modal(this.$refs.newChatModal);
      }
      this.loadUsers();
      this.modal.show();
    },
    closeNewChatModal() {
      if (this.modal) {
        this.modal.hide();
      }
    },
    async createNewChat(user) {
      const response =  await chatService.crearChat(this.yo.id, user.id);
      if (response.success) {
        await this.loadChats();
        this.selectedChat = this.chats.find(chat => chat.id == response.chat_id);
        this.seleccionarChat(this.selectedChat);
        this.closeNewChatModal();
      } else {
        if (response.tipo == 'existe') {
          Swal.fire({
            icon: 'warning',
            title: "Oops...",
            text: response.message,
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "No",
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
          }).then((result) => {
            if (result.isConfirmed) {
              this.selectedChat = this.chats.find(chat => chat.id == response.chat_id);
              this.seleccionarChat(this.selectedChat);
              this.closeNewChatModal();
            } 
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: response.message
          });
        }
      }

    },
    openProfile() {
      this.$refs.miPerfil.misDatos();
      if (!this.profileModal) {
        this.profileModal = new bootstrap.Modal(this.$refs.profileModal);
      }
      this.profileModal.show();
    },
    closeProfileModal() {
      if (this.profileModal) {
        this.profileModal.hide();
      }
    },
    openFileExplorer() {
      this.$refs.fileInput.click();
    },
    handleFileSelected(event) {
      const files = event.target.files;
      this.archivo = files[0];
      this.guardarMensaje('archivo');
    },
    async guardarMensaje(tipo) {
      const id_mio = localStorage.getItem('id_mio');
      const id_chat = localStorage.getItem('id_chat');
      const mensaje = this.newMessage;
      let response;
      if (tipo == 'archivo') {
        this.isLoading = true;

        try { 
          response = await chatService.guardarMensaje(id_mio, id_chat, mensaje, tipo, this.archivo);
          if (response.success) {
            this.isLoading = false;
          } else {
            this.isLoading = false;
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: response.message,
              showConfirmButton: false, 
              timer: 2500
            });
          }
        } catch (error) {
          this.isLoading = false;
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: error.response.statusText == 'Content Too Large' ? 'El archivo es demasiado grande' : error.response.statusText,
            showConfirmButton: false, 
            timer: 2500
          });
        }       
      } else {
        response = await chatService.guardarMensaje(id_mio, id_chat, mensaje, tipo, null);
      }
      
      if (response.success) {
        this.newMessage = '';
        
        await this.loadMessages();
        await this.loadChats();
        this.scrollToBottom();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: response.message
        });
      }

    },
    openFileModal(fileName) {
      this.selectedFile = fileName;
      if (!this.fileModal) {
        this.fileModal = new bootstrap.Modal(this.$refs.fileModal);
      }
      this.fileModal.show();
    },
    closeFileModal() {
      if (this.fileModal) {
        this.fileModal.hide();
        this.selectedFile = null;
      }
    },
    isImage(fileName) {
      const extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
      return extensions.some(ext => fileName.toLowerCase().endsWith(ext));
    },
    isPDF(fileName) {
      return fileName.toLowerCase().endsWith('.pdf');
    },
    cerrarChat() {
      this.selectedChat = null;
      localStorage.removeItem('id_chat');
      localStorage.removeItem('id_mio');
    },
    startAutoUpdate() {
      this.updateInterval = setInterval(() => {
        if (this.selectedChat) {
          this.loadMessages();
        }
        this.loadChats();
      }, 3000);
    },
    stopAutoUpdate() {
      if (this.updateInterval) {
        clearInterval(this.updateInterval);
        this.updateInterval = null;
      }
    },
    openBroadcastModal() {
      if (!this.broadcastModal) {
        this.broadcastModal = new bootstrap.Modal(this.$refs.broadcastModal);
      }
      this.loadUsers();
      this.broadcastSearchQuery = '';
      this.broadcastModal.show();
    },
    closeBroadcastModal() {
      if (this.broadcastModal) {
        this.broadcastModal.hide();
        this.broadcastMessage = '';
        this.selectedUsers = [];
        this.broadcastFile = null;
      }
    },
    openBroadcastFileExplorer() {
      this.$refs.broadcastFileInput.click();
    },
    handleBroadcastFileSelected(event) {
      this.broadcastFile = event.target.files[0];
    },
    removeBroadcastFile() {
      this.broadcastFile = null;
      this.$refs.broadcastFileInput.value = '';
    },  
    searchUsersDifusion() {
      this.availableUsersDifusion = this.users.filter(user => user.name.toLowerCase().includes(this.broadcastSearchQuery.toLowerCase()));
    },
    async sendBroadcastMessage() {
      try {
        
        const response = await chatService.enviarMensajeDifusion(this.yo.id, this.selectedUsers.join(','), this.broadcastMessage, this.broadcastFile);

        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Mensaje de difusión enviado correctamente'
          });
          this.closeBroadcastModal();

          this.loadChats();
        } else {
          throw new Error(response.message);
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo enviar el mensaje de difusión'
        });
      }
    },
    buscarContactosChats() {
      console.log(this.chats);
      this.chatsFiltrados = this.chats.filter(chat => chat.nombre.toLowerCase().includes(this.searchQuery.toLowerCase()));
    },
    verificarLogin() {
      const user = localStorage.getItem('user');
      if (!user) {
        return false;
      }
      return true;
    },
    searchUsers() {
      this.availableUsers = this.users.filter(user => user.name.toLowerCase().includes(this.newChatSearchQuery.toLowerCase()));
    }
  },
  watch: {
    newChatSearchQuery: {
      handler: function(val) {
        if (val.trim()) {
          this.searchUsers();
        } else {
          this.loadUsers();
        }
      },
      debounce: 300
    }
  },
  async mounted() {
    await this.verificarLogin();
    if (!this.verificarLogin()) {
      this.$router.push('/login');
    } else {
      await this.seleccionarMiUsuario();
      await this.loadChats();
      this.verificarChatActual();
      try {
        const users = await userService.getUsers();
      } catch (error) {
        console.error('Error al cargar contactos iniciales:', error);
      }
      this.startAutoUpdate();

      // Inicializar tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
  },
  beforeUnmount() {
    this.stopAutoUpdate();
  },
}
</script>

<style scoped>
/* Estilos personalizados */
.cursor-pointer {
  cursor: pointer;
}

.contact-item {
  margin: 10px;
  border-radius: 20px;
  background-color: rgba(117, 117, 117, 0.1);
}

.contact-item:hover {
  background-color: #c6e2ff;
}

.contact-item.active {
  background-color: #9fc9f3;
}

/* Estilos para las burbujas de chat */
.message {
  position: relative;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.22) !important;
  display: flex;
}

.message-mine {
  margin-right: 1.5%;
  border-radius: 15px 0px 15px 15px !important;
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.message-other {
  margin-left: 1.5%;
  border-radius: 0px 15px 15px 15px !important;
  background-color: rgb(170, 198, 252);
  border: 1px solid rgba(0, 0, 0, 0.1);
}

/* Estilizar scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

/* Estilos para la lista de usuarios en el modal */
.user-item {
  transition: all 0.2s ease;
}

.user-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.user-item .bi-plus-circle {
  opacity: 0;
  transition: all 0.2s ease;
}

.user-item:hover .bi-plus-circle {
  opacity: 1;
}

.file-preview {
  cursor: pointer;
  transition: all 0.2s ease;
}

.file-preview:hover {
  opacity: 0.8;
}

.modal-body img {
  max-height: 70vh;
  object-fit: contain;
}

.contenido_chat {
  height: calc(100vh - 40px) !important;
  box-shadow: -1px 4px 6px 1px rgba(0, 0, 0, 0.2);
}

.btn.rounded-circle {
  border-radius: 50% !important;
  width: 35px !important;
  height: 35px !important;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.numero_mensajes {
  margin-bottom: 10px;
  margin-left: 0px !important;
  position: absolute;
  top: 0px;
  right: 0px;
  width: 24pt;
  height: 24pt;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
}

.peso_derecha {
  width: 100%; 
  display: block; 
  text-align: right;
}

.peso_izquierda {
  width: 100%; 
  display: block; 
  text-align: left;
}
</style> 