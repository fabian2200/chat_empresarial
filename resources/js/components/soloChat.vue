<template>
    <div class="container-fluid h-100">
      <loading :active="isLoading" 
          :can-cancel="true"
          loader="bars" 
          color="#38b4c5"
          :height=100
          :width=200
          :is-full-page="true">
      </loading>
      <div class="row contenido_chat" style="overflow-y: hidden;">
        <!-- Lista de contactos (Sidebar) -->
        <div ref="listaContactosContainer" :class="!esDispositivoMovil ? 'col-2 bg-light p-0' : 'col-12 bg-light p-0'" style="background-color: #fdfdfd !important;">
          <!-- Barra de búsqueda y nuevo chat -->
          <div class="p-3 border-bottom text-center">
            <img 
                :src="baseUrl+'images/logo_empresa.png'" 
                class="rounded-circle"
                width="80%" 
                alt="logo_empresa"
            >
            <br>
            <br>
            <div class="d-flex gap-2 mb-3 justify-content-center">
              <div class="d-flex flex-column w-100">
                <h4 style="font-size: 20px; font-weight: bold; color: #1195b7;" class="mb-0">BIENVENIDO</h4>
                <br>
                <div class="info-usuario">
                  <h5 class="text-muted">{{ yo?.name }}</h5>
                  <p style="font-size: 9px; font-weight: bold; margin-bottom: 0px;" class="text-muted">{{ yo?.email }}</p>
                  <p style="font-size: 9px; font-weight: bold;" class="text-muted">{{ yo?.empresa }}</p>
                </div>
              </div>         
            </div>
          </div>
          <div class="p-3">
            <button style="width: 100%;" data-bs-toggle="tooltip"  class="btn btn-outline-danger" @click="cerrarpagina">
              <i class="fa fa-arrow-left"></i> Volver 
            </button>
          </div>
        </div>
  
        <!-- Área del chat -->
        <div ref="chatContainer" :class="!esDispositivoMovil ? 'col-10 p-0 h-100' : 'col-12 p-0 h-100'" :style="esDispositivoMovil ? 'display: none;' : 'display: block;'">
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
                  <h6 class="mb-0">{{ selectedChat.nombre }} 
                    <span v-if="!esDispositivoMovil" :class="selectedChat.online ? 'badge bg-success' : 'badge bg-primary'">
                      {{ selectedChat.online ? 'En línea' : selectedChat.last_seen }}
                    </span>
                    <span v-else :class="selectedChat.online ? 'badge bg-success position-absolute' : 'badge bg-primary position-absolute'" style="right: 6%; top: 8%;">
                      {{ selectedChat.online ? 'En línea' : selectedChat.last_seen }}
                    </span>
                  </h6>
                  <small class="text-muted">
                    {{ selectedChat.empresa }}
                  </small>
                </div>
              </div>
            </div>
  
            <!-- Área de mensajes -->
            <div 
              class="chat-messages p-3 overflow-auto"
              :style="esDispositivoMovil ? 'height: calc(87vh); overflow-x: hidden !important;' : 'height: calc(87vh - ' + altura_editable + 'px);'"
              ref="messageContainer"
            >
              <div 
                ref="dropChats"
                v-if="isDragging" class="drag-overlay"
                @dragover.prevent="handleDragOver"
                @drop="handleDrop"
              >
                Suelta los archivos aquí 📂
              </div>
  
              <div 
                v-for="message in messages" 
                :key="message.id"
                class="mb-3"
                :class="{'text-end': message.is_mine}"
              >
                <div 
                  class="message d-inline-block p-3"
                  :class="[
                    message.is_mine ? 'message-mine bg-primary text-white p-3' : 'message-other bg-white p-3',
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
                  <p v-html="message.mensaje" :style="message.is_mine ? 'color: white;' : 'color: black;'" class="mb-1 texto_en_html"></p>
                  
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
              <div class="input-group" style="align-items: center;">
                <button style="padding: 0px !important; border-color: #f1ede6 !important; background-color: #f1ede6 !important;" class="btn btn-light" @click="openFileExplorer">
                  <i class="bi bi-plus-lg" style="color: grey; font-size: 1.5rem;"></i>
                </button>
                <form @submit.prevent="guardarMensaje('texto')" 
                  @keydown.enter.exact.prevent="guardarMensaje('texto')"
                  autocomplete="off" 
                  :style="esDispositivoMovil ? 'flex-grow: 0; width: 72%; margin-right: 10px;' : 'flex-grow: 1; width: 93%;'">
                  <div contenteditable="true" @input="updateText" ref="editableDiv" class="editable"></div>
                  <!-- <input
                    style="border: 1px solid #c3c3c3; width: 98%;"
                    type="text" 
                    class="form-control mx-2" 
                    placeholder="Escribe un mensaje..."
                    v-model="newMessage"
                  > -->
                </form>
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
  
      <!-- Modal Bootstrap para archivos arrastrados -->
      <div class="modal fade" id="fileModalCopy" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="fileModalLabel">Archivos Cargados</h5>
            </div>
            <div class="modal-body">
              <ul class="list-group">
                <li v-for="(file, index) in files" :key="index" class="list-group-item d-flex justify-content-between align-items-center">
                  {{ file.name }} ({{ formatSize(file.size) }})
                  <button class="btn btn-light btn-sm" @click="removeFile(index)">❌</button>
                </li>
              </ul>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" @click="files = []" data-bs-dismiss="modal">Cancelar <i class="bi bi-x"></i></button>
              <button type="button" class="btn btn-success" @click="sendFiles">Enviar <i class="bi bi-send"></i></button>
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
  import { baseUrl } from '../baseUrl';
  import Loading from 'vue3-loading-overlay';
  import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';
  
  export default {
    components: {
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
        isLoading: false,
        esDispositivoMovil: false,
        erroresArchivo: [],
        altura_editable: 78,
        isDragging: false,
        files: [],
      }
    },
    methods: {
      verificarChatActual() {
        const id_mio = this.$route.params.id_mio;
        const id_amigo = this.$route.params.id_amigo;

        if (id_mio && id_amigo) {
          const chat = this.chats.find(chat => (chat.id_crea == id_mio && chat.id_amigo == id_amigo) || (chat.id_crea == id_amigo && chat.id_amigo == id_mio));
          if(chat){
            this.seleccionarChat(chat);
          }else{
            Swal.fire({
              icon: 'error',
              html: '<h5>No se encontró chat con este usuario <br> <br>¿Desea crear uno?</h5>',
              showConfirmButton: true, 
              confirmButtonText: 'Crear chat',
              confirmButtonColor: '#3085d6',
              showCancelButton: true,
              cancelButtonText: 'Cancelar',
              cancelButtonColor: '#d33',
              allowOutsideClick: false,
            }).then((result) => {
              if (result.isConfirmed) {
                this.createNewChat(id_mio, id_amigo);
              }else{
               this.cerrarpagina();
              }
            });
          }
        }
      },
      cerrarpagina(){
        localStorage.clear();
        this.$router.push('/login');
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
        localStorage.setItem('redirect_id_chat', chat.id);
        localStorage.setItem('redirect_id_mio', this.yo.id);
        
        await this.loadMessages(chat.id);
        await this.loadChats();
  
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      },
      async loadMessages() {
        const id_mio = localStorage.getItem('redirect_id_mio');
        const id_chat = localStorage.getItem('redirect_id_chat');
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
      async createNewChat(id_amigo, id_mio) {
        const response =  await chatService.crearChat(id_mio, id_amigo);
        if (response.success) {
          await this.loadChats();
          this.selectedChat = this.chats.find(chat => chat.id_crea == id_mio && chat.id_amigo == id_amigo);
          this.seleccionarChat(this.selectedChat); 
        } else {
          this.selectedChat = this.chats.find(chat => chat.id_crea == id_mio && chat.id_amigo == id_amigo);
          this.seleccionarChat(this.selectedChat);
        }
      },
      openFileExplorer() {
        this.$refs.fileInput.click();
      },
      async handleFileSelected(event) {
        const files = event.target.files;
        if (files.length > 0) {
          this.isLoading = true;
          const maxFileSize = 100 * 1024 * 1024;
          for(var i = 0; i < files.length; i++){
            if (files[i].size > maxFileSize) {
              this.erroresArchivo.push('<li style="color: red;">'+ files[i].name+' es demasiado grande, el tamaño máximo es de 100 MB</li>');
            }else{
              this.archivo = files[i];
              await this.guardarMensajeArchivo('archivo', this.archivo);
            }
          }
  
          var mensaje_swal = '<ul>';
          for(var i = 0; i < this.erroresArchivo.length; i++){
            mensaje_swal += this.erroresArchivo[i];
          }
          mensaje_swal += '</ul>';
  
          Swal.fire({
            icon: 'warning',
            title: 'Atención',
            html: mensaje_swal,
            showConfirmButton: true, 
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#3085d6',
          });
  
          this.isLoading = false;
          this.erroresArchivo = [];
          this.$refs.fileInput.value = '';
        }
      },
      async guardarMensajeArchivo(tipo, archivo) {
        const id_mio = localStorage.getItem('redirect_id_mio');
        const id_chat = localStorage.getItem('redirect_id_chat');
        const mensaje = this.newMessage;
        
        try { 
          let response = await chatService.guardarMensaje(id_mio, id_chat, mensaje, tipo, this.archivo);
          if (response.success == false) {
            this.erroresArchivo.push('<li style="color: red;">'+response.message+'</li>');
          }else{
            this.erroresArchivo.push('<li style="color: green;">'+archivo.name+' subido correctamente</li>');
          }
        } catch (error) {
          this.erroresArchivo.push(error.response.statusText == 'Content Too Large' ? '<li style="color: red;">El archivo '+this.archivo.name+' es demasiado grande</li>' : '<li style="color: red;">'+error.response.statusText+'</li>');
        }       
      },
      async guardarMensaje(tipo) {
        const id_mio = localStorage.getItem('redirect_id_mio');
        const id_chat = localStorage.getItem('redirect_id_chat');
       
        const vacio = this.$refs.editableDiv.textContent.trim();
        
        if(vacio != ''){ 
          const mensaje = this.newMessage;
          let response = await chatService.guardarMensaje(id_mio, id_chat, mensaje, tipo, null); 
          if (response.success) {
            this.newMessage = '';
            this.$refs.editableDiv.innerHTML = '';
            this.altura_editable = 78;
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
        }else{
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'No se puede enviar un mensaje vacío',
            showConfirmButton: false, 
            timer: 2500
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
      startAutoUpdate() {
        this.updateInterval = setInterval(() => {
          if (this.selectedChat) {
            this.loadMessages();
          }
        }, 3000);
      },
      stopAutoUpdate() {
        if (this.updateInterval) {
          clearInterval(this.updateInterval);
          this.updateInterval = null;
        }
      },
      async verificarLogin() {
        const id_mio = this.$route.params.id_mio;
        const user = await userService.obtenerMiUsuario(id_mio);
        if(user){
          this.yo = user;
          localStorage.setItem('redirect_user', JSON.stringify(this.yo));
          
          await this.verificarSiEsDispositivoMovil();
          await this.loadChats();
          this.verificarChatActual();
          this.startAutoUpdate();
        }
      },
      verificarSiEsDispositivoMovil() {
        const userAgent = navigator.userAgent;
        var test = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent);
        if (test) {
          this.esDispositivoMovil = true;
        } else {
          this.esDispositivoMovil = false;
        }
      },
      mostrarChatContainer() {
        if (this.esDispositivoMovil) {
          console.log('Es un dispositivo móvil');
          this.$refs.chatContainer.style.display = 'block'; 
          this.$refs.listaContactosContainer.style.display = 'none';
        }
      },
      ocultarChatContainer() {
        if (this.esDispositivoMovil) {
          console.log('Es un dispositivo móvil');
          this.$refs.chatContainer.style.display = 'none'; 
          this.$refs.listaContactosContainer.style.display = 'block';
        }
      },
      updateText(event) {
        this.altura_editable = this.$refs.editableDiv.offsetHeight + 32;
        this.newMessage = event.target.innerHTML;
      },
      handleWindowDragEnter(event) {
        if (event.dataTransfer.types.includes("Files")) {
          this.isDragging = true;
        }
      },
      handleWindowDragLeave(event) {
        if (event.clientY <= 0 || event.clientX <= 0 || event.clientX >= window.innerWidth || event.clientY >= window.innerHeight) {
          this.isDragging = false;
        }
      },
      handleDragOver(event) {
        event.preventDefault();
      },
      handleDrop(event) {
        event.preventDefault();
        this.isDragging = false;
  
        if (!this.$refs.dropChats || !this.$refs.dropChats.contains(event.target)) {
            alert('No se puede enviar archivos fuera del chat');
            return;
        }
  
        const droppedFiles = Array.from(event.dataTransfer.files);
        this.files = [...this.files, ...droppedFiles];
  
        const modal = new bootstrap.Modal(document.getElementById('fileModalCopy'));
        modal.show();
      },
      removeFile(index) {
        this.files.splice(index, 1);
      },
      formatSize(size) {
        return (size / 1024).toFixed(2) + " KB";
      },
      async sendFiles() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('fileModalCopy'));
        modal.hide();
        const files = this.files;
        if (files.length > 0) {
          this.isLoading = true;
          const maxFileSize = 100 * 1024 * 1024;
          for(var i = 0; i < files.length; i++){
            if (files[i].size > maxFileSize) {
              this.erroresArchivo.push('<li style="color: red;">'+ files[i].name+' es demasiado grande, el tamaño máximo es de 100 MB</li>');
            }else{
              this.archivo = files[i];
              await this.guardarMensajeArchivo('archivo', this.archivo);
            }
          }
  
          var mensaje_swal = '<ul>';
          for(var i = 0; i < this.erroresArchivo.length; i++){
            mensaje_swal += this.erroresArchivo[i];
          }
          mensaje_swal += '</ul>';
  
          Swal.fire({
            icon: 'warning',
            title: 'Atención',
            html: mensaje_swal,
            showConfirmButton: true, 
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#3085d6',
          });
  
          this.isLoading = false;
          this.erroresArchivo = [];
          this.files = [];
          await this.loadMessages();
          await this.loadChats();
          this.$nextTick(() => {
            this.scrollToBottom();
          });
        }
      },
      limpiarLocalStorage() {
        localStorage.removeItem('redirect_user');
        localStorage.removeItem('redirect_id_chat');
        localStorage.removeItem('redirect_id_mio');
      }
    },
    async mounted() {  
      localStorage.setItem('redirect_user', JSON.stringify({id: this.$route.params.id_mio}));
      this.verificarLogin();
      
      window.addEventListener("dragenter", this.handleWindowDragEnter);
      window.addEventListener("dragleave", this.handleWindowDragLeave);
      window.addEventListener("dragover", (event) => event.preventDefault());
      window.addEventListener("drop", (event) => {
        this.isDragging = false;
        event.preventDefault(); // Previene la apertura del archivo en otra pestaña
      });

      window.addEventListener('beforeunload', this.limpiarLocalStorage);
    },
    beforeUnmount() {
      window.removeEventListener('beforeunload', this.limpiarLocalStorage);

      this.stopAutoUpdate();
      window.removeEventListener("dragenter", this.handleWindowDragEnter);
      window.removeEventListener("dragleave", this.handleWindowDragLeave);
      window.removeEventListener("dragover", (event) => event.preventDefault());
      window.removeEventListener("drop", (event) => {
        event.preventDefault(); // Previene la apertura del archivo en otra pestaña
      });
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
      background: linear-gradient(to right, #007bff, #0061ca);
  }
  
  .message-mine .texto_en_html ::v-deep(p){
    margin-bottom: 0px !important;
    color: white !important;
    font-size: 14px !important;
    text-align: left !important;
    text-indent: 0pt !important;
  }
  
  .message-mine .texto_en_html ::v-deep(span){
    color: white !important;
  }
  
  .message-mine .texto_en_html ::v-deep(strong){
    color: white !important;
  }
  
  .message-other {
    margin-left: 1.5%;
    border-radius: 0px 15px 15px 15px !important;
    background-color: rgb(170, 198, 252);
    border: 1px solid rgba(63, 63, 63, 0.1);
  }
  
  .message-other .texto_en_html ::v-deep(p){
    margin-bottom: 0px !important;
    color: rgb(54, 54, 54) !important;
    font-size: 14px !important;
    text-align: left !important;
    text-indent: 0pt !important;
  }
  
  .message-other .texto_en_html ::v-deep(span){
    color: rgb(54, 54, 54) !important;
  }
  
  .message-other .texto_en_html ::v-deep(strong){
    color: rgb(54, 54, 54) !important;
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
    height: calc(100vh) !important;
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
  
  .editable {
    border: 1px solid #ccc;
    padding-left: 18px;
    padding-right: 18px;
    padding-top: 10px;
    padding-bottom: 10px;
    background-color: #ffffff;
    border-radius: 10px;
    margin-left: 10px;
    margin-right: 10px;
    min-height: 35px;
    max-height: 200px;
    overflow-y: auto;
  }
  
  .editable ::v-deep(p) {
    text-indent: 0pt !important;
    margin-bottom: 0px !important;
  }
  
  .drag-overlay {
    position: sticky;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgb(255 255 255 / 88%);
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 1.5em;
      color: #333;
      font-weight: bold;
      width: 100%;
      height: 100%;
      border-radius: 10px;
      z-index: 1000;
      border: 3px dashed #0d6efd;
  }

  .info-usuario{
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 10px;
    background-color: #f0f0f0;
    width: 100%;
    text-align: center;
  }
  </style> 