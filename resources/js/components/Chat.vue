<template>
  <div class="container-fluid h-100">
    <div class="row h-100">
      <!-- Lista de contactos (Sidebar) -->
      <div class="col-4 col-md-3 bg-light border-end p-0" style="background-color: #d3e5ff !important;">
        <!-- Barra de búsqueda y nuevo chat -->
        <div class="p-3 border-bottom">
          <div class="d-flex gap-2 mb-3 justify-content-end">
            <button class="btn btn-success" @click="openNewChat">
              <i class="fa-solid fa-comment-medical"></i>
            </button>
            <button class="btn btn-secondary" @click="openProfile">
              <i class="bi bi-gear-fill"></i>
            </button>
            <button class="btn btn-danger" @click="logout">
              <i class="bi bi-box-arrow-right me-2"></i>Salir
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
            >
          </div>
        </div>

        <!-- Lista de contactos -->
        <div class="contacts-list overflow-auto" style="height: calc(100vh - 130px);">
          <div 
            v-for="chat in chats" 
            :key="chat.id"
            class="contact-item p-3 border-bottom cursor-pointer"
            :class="{'active': selectedChat?.id === chat.id}"
            @click="seleccionarChat(chat)"
          >
            <div class="d-flex align-items-center">
              <div class="position-relative">
                <img 
                  :src="'/images/'+chat.avatar+'.png'" 
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
              <div class="ms-3 flex-grow-1">
                <small v-if="chat.mensajes_sin_leer > 0" class="badge bg-danger ms-2" style="margin-bottom: 10px; margin-left: 0px !important;">
                  {{ chat.mensajes_sin_leer }} Mensajes nuevos
                </small>
                
                <h6 class="mb-0">
                  {{ chat.nombre }} 
                  <span :class="chat.online ? 'badge bg-success' : 'badge bg-primary'">
                    {{ chat.online ? 'En línea' : 'Activo ' + chat.last_seen }}
                  </span>
                </h6>
                <small class="text-muted">{{ chat.empresa }}</small>
                <hr>
                <small class="text-muted">
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
          <div class="chat-header p-3 border-bottom bg-white">
            <div class="d-flex align-items-center">
              <img 
                :src="'/images/'+selectedChat.avatar+'.png'" 
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
            class="chat-messages p-3 overflow-auto bg-light"
            style="height: calc(100vh - 140px);"
            ref="messageContainer"
          >
            <div 
              v-for="message in messages" 
              :key="message.id"
              class="mb-3"
              :class="{'text-end': message.is_mine}"
            >
              <div 
                class="message d-inline-block p-3 shadow-sm"
                :class="[
                  message.is_mine ? 'message-mine bg-primary text-white' : 'message-other bg-white',
                  {'rounded-bottom-end-0': message.is_mine},
                  {'rounded-bottom-start-0': !message.is_mine}
                ]"
                style="max-width: 75%;"
              >
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
          <div class="chat-input p-3 border-top bg-white">
            <div class="input-group">
              <button class="btn btn-light" @click="openFileExplorer">
                <i class="bi bi-plus-lg"></i>
              </button>
              <input 
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
                  :src="'/images/'+user.avatar+'.png'" 
                  class="rounded-circle"
                  width="40" 
                  height="40"
                  :alt="user.name"
                >
                <div class="ms-3">
                  <h6 class="mb-0">{{ user.name }} <span :class="user.online ? 'badge bg-success' : 'badge bg-primary'">{{ user.online ? 'En línea' : 'Activo ' + user.lastSeen }}</span></h6>
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
            <form @submit.prevent="updateProfile">
              <div class="text-center mb-4">
                <img 
                  :src="'/images/'+yo?.avatar+'.png'" 
                  class="rounded-circle mb-3"
                  width="100" 
                  height="100"
                  :alt="yo?.name"
                >
                <div class="mb-3">
                  <select class="form-select" v-model="profileData.avatar">
                    <option value="avatar1"></option>
                    <option value="avatar2"></option>
                    <option value="avatar3"></option>
                    <option value="avatar4"></option>
                  </select>
                </div>
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
                :src="'/archivos/' + selectedFile" 
                class="img-fluid mb-3"
                alt="Vista previa"
              >
              <!-- PDFs -->
              <iframe
                v-else-if="isPDF(selectedFile)"
                :src="'/archivos/' + selectedFile"
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
              :href="'/archivos/' + selectedFile" 
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
  </div>
</template>

<script>
import * as bootstrap from 'bootstrap'
import { userService, chatService } from '../services/api' 
import Swal from 'sweetalert2'

export default {
  name: 'Chat',
  data() {
    return {
      searchQuery: '',
      selectedChat: null,
      newMessage: '',
      chats: [],
      messages: [
       
      ],
      newChatSearchQuery: '',
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
      updateInterval: null
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
      try {
        await userService.logout(this.yo.id);
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      }
    },
    async loadUsers() {
      try {
        const users = await userService.getUsers();
        this.availableUsers = users.filter(user => user.id != this.yo.id);
        console.log(this.availableUsers);
      } catch (error) {
        console.error('Error al cargar usuarios:', error);
      }
    },
    async loadChats() {
      try {
        const chats = await chatService.obtenerChatsUsuario(this.yo.id);
        this.chats = chats.chats;
      } catch (error) {
        console.error('Error al cargar chats:', error);
      }
    },
    seleccionarChat(chat) {
      this.selectedChat = chat;
      localStorage.setItem('id_chat', chat.id);
      localStorage.setItem('id_mio', this.yo.id);
      this.$nextTick(() => {
        this.scrollToBottom();
      });
      
      this.loadMessages(chat.id);
      this.loadChats();
    },
    async loadMessages() {
      const id_mio = localStorage.getItem('id_mio');
      const id_chat = localStorage.getItem('id_chat');
      const messages = await chatService.obtenerMensajesChat(id_mio, id_chat);
      this.messages = messages.mensajes;
      
      this.$nextTick(() => {
        this.scrollToBottom();
      });
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
        this.closeNewChatModal();
        this.loadChats();
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
      if (!this.profileModal) {
        this.profileModal = new bootstrap.Modal(this.$refs.profileModal);
      }
      this.profileData = {
        name: this.yo.name,
        email: this.yo.email,
        empresa: this.yo.empresa,
        telefono: this.yo.telefono,
        avatar: this.yo.avatar,
        password: ''
      };
      this.profileModal.show();
    },
    closeProfileModal() {
      if (this.profileModal) {
        this.profileModal.hide();
      }
    },
    async updateProfile() {
      try {
        this.closeProfileModal();
        this.yo = { ...this.yo, ...this.profileData };
        localStorage.setItem('user', JSON.stringify(this.yo));
      } catch (error) {
        console.error('Error al actualizar perfil:', error);
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
        response = await chatService.guardarMensaje(id_mio, id_chat, mensaje, tipo, this.archivo);
      } else {
        response = await chatService.guardarMensaje(id_mio, id_chat, mensaje, tipo, null);
      }
      
      if (response.success) {
        this.newMessage = '';
        this.scrollToBottom();
        this.loadMessages();
        this.loadChats();
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
    await this.seleccionarMiUsuario();
    await this.loadChats();
    this.verificarChatActual();
    try {
      const users = await userService.getUsers();
    } catch (error) {
      console.error('Error al cargar contactos iniciales:', error);
    }
    this.startAutoUpdate();
  },
  beforeUnmount() {
    this.stopAutoUpdate();
  },
}
</script>

<style>
/* Estilos personalizados */
.cursor-pointer {
  cursor: pointer;
}

.contact-item {
  margin: 10px;
  border-radius: 10px;
  background-color: #ffffff;
}

.contact-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.contact-item.active {
  background-color: rgba(0, 0, 0, 0.1);
}

/* Estilos para las burbujas de chat */
.message {
  position: relative;
  transition: all 0.2s ease;
}

.message-mine {
  border-radius: 10px 10px 0px 10px !important;
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.message-other {
  border-radius: 10px 10px 10px 0px !important;
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
</style> 