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
            <div class="col-4 col-md-3 bg-light p-0" style="background-color: #fdfdfd !important;">
                <!-- Barra de búsqueda y nuevo chat -->
                <div class="p-3 border-bottom">
                    <div class="d-flex gap-2 mb-3 justify-content-end">
                        
                        <div class="d-flex flex-column w-100">
                            <h5 class="mb-0">Bienvenido</h5>
                            <small class="text-muted">{{ yo?.name }}</small>
                        </div>
                        <button data-bs-toggle="tooltip" data-bs-placement="bottom" title="Nuevo Grupo" class="btn btn-outline-success rounded-circle" @click="openNewChat">
                            <i class="bi bi-chat-square-dots-fill"></i>
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
                        <input type="text" class="form-control border-start-0" placeholder="Buscar grupos..."
                            v-model="searchQuery">
                    </div>
                </div>
                <div class="contacts-list overflow-auto" style="height: calc(100vh - 180px);">
                    <div 
                        v-for="group in groupsFiltered" 
                        :key="group.id"
                        class="contact-item p-2  cursor-pointer"
                        :class="{'active': groupSelected?.id === group.id}"
                        @click="selectGroup(group)"
                    >
                        <div class="d-flex align-items-center elemento">
                            <div class="ms-3 flex-grow-1">
                                <small style="font-size: 12px;" class="mb-2 badge bg-warning text-dark">Participantes: {{ group.participantes }}</small>
                                <h5 class="mb-1">
                                    {{ group.detalle_grupo.nombre }} 
                                </h5>
                                <small class="text-muted" style="font-size: 10px;">
                                    Creado el {{ group.detalle_grupo.fecha }} a las {{ group.detalle_grupo.hora }} 
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área del chat -->
            <div class="col-8 col-md-9 p-0 h-100">
                <template v-if="groupSelected">
                    <!-- Encabezado del chat -->
                    <div class="chat-header p-3 border-bottom bg-white" style="background-color: #e1e1e1 !important;">
                        <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <h6 class="mb-0">
                                    {{ groupSelected.detalle_grupo.nombre }} 
                                    <small @click="verParticipantes" style="cursor: pointer;" class="text-muted badge bg-warning text-light">
                                        Participantes: {{ groupSelected.participantes }}
                                    </small>
                                </h6>
                                <small class="text-muted">
                                    Creado el {{ groupSelected.detalle_grupo.fecha }} a las {{ groupSelected.detalle_grupo.hora }} 
                                </small>                            
                            </div>
                            <div class="ms-auto">
                                <button class="btn btn-danger me-2" @click="cerrarChat">
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
                            class="mb-3 d-flex align-items-start"
                            :class="{'flex-row-reverse': message.is_mine}"
                        >
                            <img 
                                :src="baseUrl+'images/'+message.avatar" 
                                alt="Foto de perfil" 
                                class="rounded-circle me-2" 
                                style="background-color: white; padding: 3px;margin-left: 10px ;width: 40px; height: 40px; object-fit: cover; margin-top: 15px;"
                            >
                            <div 
                                class="message d-inline-block p-2"
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
                                <div v-else style="position: absolute; left: -25px; top: 0px; z-index: 99;">
                                    <img :src="baseUrl+'images/other.png'" style="width: 30px; height: 50px;">
                                </div>
                                <small :style="message.is_mine ? 'color: white; font-size: 10px;' : 'color: grey; font-size: 10px;'">
                                   {{ message.usuario }}
                                </small>
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
                    <div v-if="groupSelected?.estado == 1" class="chat-input p-3" style="background-color: #f1ede6 !important;">
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
                    <div v-else class="chat-input p-3" style="background-color: #f1ede6 !important;">
                        <div class="text-center text-muted">
                            <h5>No puedes enviar mensajes en este grupo, ya que has sido desactivado</h5>
                        </div>
                    </div>
                </template>
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
    </div>

    <!-- Modal de creación de grupo -->
    <div class="modal" id="newGroupModal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Grupo</h5>
                    <button type="button" @click="closeModal" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="groupName" style="font-weight: bold;" class="form-label">Nombre del Grupo</label>
                        <input type="text" class="form-control" id="groupName" v-model="groupName">
                    </div>
                    <div class="mb-3">
                        <label for="groupMembers" style="font-weight: bold;" class="form-label">Miembros del Grupo</label>
                        <div class="row">
                            <div 
                                v-for="user in users" 
                                :key="user.id"
                                class="col-lg-3"
                            >
                                <div class="user-item d-flex align-items-center rounded cursor-pointer">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            :value="user.id"
                                            v-model="groupMembers"
                                    >
                                    </div> 
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ user.name }}</h6>
                                        <small class="text-muted">{{ user.empresa }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click="closeModal">
                            Cancelar <i class="bi bi-x"></i>
                        </button>
                        <button type="button" :disabled="!groupName || !groupMembers.length" class="btn btn-success" @click="createGroup">
                            Crear Grupo <i class="bi bi-save"></i>
                        </button>
                    </div>
                </div>  
            </div>
        </div>
    </div>


    <input 
      type="file" 
      ref="fileInputII" 
      class="d-none" 
      @change="handleFileSelected" 
    >

    <!-- Modal de vista previa del archivo -->
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

    <!-- Modal de vista previa de participantes -->
    <div class="modal" id="participantesModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Participantes del grupo ({{groupSelected?.detalle_grupo.nombre }})</h5>
                    <button  @click="closeModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="groupSelected?.es_admin" class="d-flex justify-content-end">
                        <button  class="btn btn-success mb-3" @click="agregarParticipante">Agregar participante <i class="bi bi-plus"></i></button>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="participante" v-for="participante in groupSelected?.detalle_participantes" :key="participante.id">
                                <div class="d-flex flex-column">
                                    <h5 class="mb-0">{{ participante.name }}</h5>
                                    <small class="text-muted">{{ participante.empresa }}</small>
                                    <small class="text-muted" v-if="participante.es_admin">Administrador del grupo</small>
                                </div>
                                <div class="ms-auto" v-if="groupSelected.es_admin && participante.id !== yo.id">
                                    <button v-if="participante.estado == 1" class="btn btn-danger rounded-circle" @click="eliminarParticipante(participante.id)"><i class="bi bi-trash"></i></button>
                                    <button v-else class="btn btn-success rounded-circle" @click="reactivarParticipante(participante.id)"><i class="bi bi-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de agregar participante -->
    <div class="modal" id="agregarParticipanteModal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar participante</h5>
                    <button  @click="closeModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="groupMembers" style="font-weight: bold;" class="form-label">Agregar Participantes al grupo</label>
                            <div class="row">
                                <div 
                                    v-for="user in participantesNoAgregados" 
                                    :key="user.id"
                                    class="col-lg-3"
                                >
                                    <div class="user-item d-flex align-items-center rounded cursor-pointer">
                                        <div class="form-check">
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input" 
                                                :value="user.id"
                                                v-model="groupMembersAgregar"
                                        >
                                        </div> 
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ user.name }}</h6>
                                            <small class="text-muted">{{ user.empresa }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="closeModal">Cancelar <i class="bi bi-x"></i></button>
                    <button type="button" class="btn btn-success" @click="agregarParticipantes">Agregar <i class="bi bi-plus"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="miPerfilModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mi Perfil</h5>
                    <button @click="closeProfile" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <miPerfil ref="miPerfil" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { userService, grupoService } from '../services/api' 
import Swal from 'sweetalert2'
import * as bootstrap from 'bootstrap'
import miPerfil from './miPerfil.vue'
import { baseUrl } from '../baseUrl';
import Loading from 'vue3-loading-overlay';
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';

export default {
    components: {
        miPerfil,
        Loading
    },
    data() {
        return {
            searchQuery: '',
            yo: JSON.parse(localStorage.getItem('user')),
            users: [],
            modal: null,
            groupName: '',
            groupMembers: [],
            groups: [],
            groupSelected: null,
            groupsFiltered: [],
            fileExplorer: false,
            newMessage: '',
            messages: [],
            messageInput: '',
            archivo: null,
            updateInterval: null,
            selectedFile: null,
            fileModal: null,
            participantesNoAgregados: [],
            groupMembersAgregar: [],
            profileModal: null,
            baseUrl: baseUrl,
            isLoading: false
        }
    },
    async mounted() {
        if(!this.verificarLogin()){
            this.$router.push('/login');
        }else {
            await this.loadGroups();
            await this.verificarGrupoActual();
            this.startAutoUpdate();

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
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
        verificarGrupoActual() {
            const id_grupo = localStorage.getItem('id_grupo');
            if (id_grupo) {
                const grupo = this.groups.find(group => group.id_grupo == id_grupo);
                this.selectGroup(grupo);
            }
        },
        async openNewChat() {
            this.modal = new bootstrap.Modal(document.getElementById('newGroupModal'));
            await this.loadUsers();
            this.modal.show();
        },
        async loadUsers() {
            await userService.getUsers().then(response => {
                this.users = response;
                this.users = this.users.filter(user => user.id !== this.yo.id);
            })
            .catch(error => {
                console.error('Error al cargar usuarios:', error);
            });
        },
        closeModal() {
            this.modal.hide();
            this.groupName = '';
            this.groupMembers = [];
            this.groupMembersAgregar = [];
        },
        createGroup() {
            this.groupMembers = this.groupMembers.join(',');
            grupoService.crearGrupo(this.yo.id, this.groupName, this.groupMembers).then(response => {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Grupo creado',
                        text: response.message
                    });
                    this.closeModal();
                    this.loadGroups();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error al crear grupo',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        },
        async loadGroups() {
            await grupoService.obtenerGruposUsuario(this.yo.id).then(response => {
                this.groups = response.grupos;
                this.groupsFiltered = this.groups;
                var id_grupo = localStorage.getItem('id_grupo');
                if(id_grupo){
                    let grupo = this.groups.find(group => group.id_grupo == id_grupo);
                    this.groupSelected = grupo;
                }
            });
        },
        filterGroups() {
            this.groupsFiltered = this.groups.filter(group => group.detalle_grupo.nombre.toLowerCase().includes(this.searchQuery.toLowerCase()));
        },
        async selectGroup(group) {
            this.groupSelected = group;
            localStorage.setItem('id_grupo', group.id_grupo);
            localStorage.setItem('id_mio', this.yo.id);
            await this.loadMessages();
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },
        cerrarChat() {
            this.groupSelected = null;
            localStorage.removeItem('id_grupo');
            localStorage.removeItem('id_mio');
        },
        openFileExplorer() {
            this.$refs.fileInputII.click();
        },
        async handleFileSelected(event) {
            console.log(event);
            const files = event.target.files;
            if (files.length > 0) {
                console.log(files);
                const maxFileSize = 100 * 1024 * 1024;
                if (files[0].size > maxFileSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo demasiado grande',
                        text: 'El archivo no debe pesar más de 100 MB',
                        showConfirmButton: false,
                        timer: 2500
                    });
                    this.$refs.fileInputII.value = '';
                    return;
                }
                this.archivo = files[0];
                await this.guardarMensaje('archivo');
                this.$refs.fileInputII.value = '';
            }
        },
        async guardarMensaje(tipo) {
            const id_mio = localStorage.getItem('id_mio');
            const id_grupo = localStorage.getItem('id_grupo');
            const mensaje = this.newMessage;
            let response;
            if (tipo == 'archivo') {
                this.isLoading = true;
                try {
                    response = await grupoService.guardarMensaje(id_mio, id_grupo, mensaje, tipo, this.archivo);
                    if (response.success) {
                        this.isLoading = false;
                    } else {
                        this.isLoading = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
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
                response = await grupoService.guardarMensaje(id_mio, id_grupo, mensaje, tipo, null);
            }
            
            if (response.success) {
                this.newMessage = '';
                await this.loadMessages();
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: response.message
                });
            }
        },
        async loadMessages() {
            const id_mio = localStorage.getItem('id_mio');
            const id_grupo = localStorage.getItem('id_grupo');
            if (id_mio && id_grupo) {
                await grupoService.obtenerMensajesGrupo(id_mio, id_grupo).then(response => {
                    this.messages = response.mensajes;
                });
            }
        },
        scrollToBottom() {
            this.$nextTick(() => {
                this.$refs.messageContainer.scrollTop = this.$refs.messageContainer.scrollHeight;
            });
        },
        startAutoUpdate() {
            this.updateInterval = setInterval(() => {
                if (this.groupSelected) {
                    this.loadMessages();
                }
                this.loadGroups();
            }, 3000);
        },
        stopAutoUpdate() {
            clearInterval(this.updateInterval);
        },
        openFileModal(fileName) {
            this.selectedFile = fileName;
            if (!this.fileModal) {
                this.fileModal = new bootstrap.Modal(this.$refs.fileModal);
            }
            this.fileModal.show();
        },
        closeFileModal() {
            this.fileModal.hide();
        },
        isImage(fileName) {
            const extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
            return extensions.some(ext => fileName.toLowerCase().endsWith(ext));
        },
        isPDF(fileName) {
            return fileName.toLowerCase().endsWith('.pdf');
        },
        verParticipantes() {
            this.modal = new bootstrap.Modal(document.getElementById('participantesModal'));
            this.modal.show();
        },
        async logout() {
            Swal.fire({ 
                icon: 'warning',
                title: '¿Estás seguro?',
                text: '¿Estás seguro de querer cerrar sesión?',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                  userService.logout(this.yo.id);
                }
            });
        },
        async eliminarParticipante(id_participante) {
            const id_grupo = localStorage.getItem('id_grupo');
            await grupoService.eliminarParticipante(this.yo.id, id_grupo, id_participante);
            await this.loadGroups();
            const grupo = this.groups.find(group => group.id_grupo == id_grupo);
            this.groupSelected = grupo;
            console.log(this.groupSelected);
        },
        async reactivarParticipante(id_participante) {
            const id_grupo = localStorage.getItem('id_grupo');
            await grupoService.reactivarParticipante(this.yo.id, id_grupo, id_participante);
            await this.loadGroups();
            const grupo = this.groups.find(group => group.id_grupo == id_grupo);
            this.groupSelected = grupo;
        },
        async agregarParticipante() {
            this.modal.hide();
            await this.loadUsers();
            this.participantesNoAgregados = this.users.filter(user => !this.groupSelected.detalle_participantes.some(participante => participante.id === user.id));
            console.log(this.participantesNoAgregados);
            this.agregarParticipanteModal();
        },
        agregarParticipanteModal() {
            this.modal = new bootstrap.Modal(document.getElementById('agregarParticipanteModal'));
            this.modal.show();
        },
        async agregarParticipantes() {
            const id_grupo = localStorage.getItem('id_grupo');
            var response = await grupoService.agregarParticipantes(this.yo.id, id_grupo, this.groupMembersAgregar);
            if(response.success){
                Swal.fire({
                    icon: 'success',
                    title: 'Participantes agregados',
                    text: response.message
                });

                await this.loadGroups();
                const grupo = this.groups.find(group => group.id_grupo == id_grupo);
                this.groupSelected = grupo;
                this.closeModal();
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: response.message
                });
            }
        },
        openProfile() {
            this.$refs.miPerfil.misDatos();
            this.profileModal = new bootstrap.Modal(document.getElementById('miPerfilModal'));
            this.profileModal.show();
        },
        closeProfile() {
            this.profileModal.hide();
        }
    },
    beforeUnmount() {
        this.stopAutoUpdate();
    }
}
</script>
<style scoped>
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
}

.message-mine {
    border-radius: 15px 0px 15px 15px !important;
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.message-other {
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

.user-item {
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    background-color: #f0f0f0;
    margin: 5px;
}

.user-item:hover {
    background-color: #d0d0d0;
}

.user-item .form-check {
    min-height: auto !important;
}

.user-item .form-check-input {
    margin-top: 0;
    scale: 2;
    margin-left: -10px !important;
}

.user-item .form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.user-item .form-check-input:checked:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    cursor: pointer;
}

.participante {
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    background-color: #f0f0f0;
    margin: 5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.participante:hover {
    background-color: #d0d0d0;
    cursor: pointer;
}


.tooltip-inner {
  background-color: #e0e0e093 !important;
  color: #575757 !important; 
  border: 1px solid #333 !important;
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
