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
            <div ref="listaContactosContainer" :class="!esDispositivoMovil ? 'col-4 col-md-3 bg-light p-0' : 'col-12 bg-light p-0'" style="background-color: #fdfdfd !important;">
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
                        <form autocomplete="off" :style="!esDispositivoMovil ? 'flex-grow: 1;' : 'flex-grow: 0; width: 85%;'">
                            <input 
                                type="text" 
                                class="form-control border-start-0" 
                                placeholder="Buscar grupos..."
                                v-model="searchQuery"
                            >
                        </form>
                    </div>
                </div>
                <div class="contacts-list overflow-auto" :style="esDispositivoMovil ? 'height: calc(100vh - 25vh); overflow-x: hidden !important;' : 'height: calc(100vh - 180px);'">
                    <div 
                        v-for="group in groupsFiltered" 
                        :key="group.id"
                        class="contact-item p-2  cursor-pointer"
                        :class="{'active': groupSelected?.id === group.id}"
                        @click="selectGroup(group) ; mostrarChatContainer()"
                    >
                        <div class="d-flex align-items-center elemento" style="z-index: 1;">
                            <div class="ms-2" style="width: calc(100% - 1.2rem); position: relative;">
                                <small style="font-size: 12px; position: absolute; top: -1px; right: -8px;" class="mb-2 badge bg-warning text-dark"><i class="bi bi-people-fill"></i> {{ group.participantes }}</small>
                                <small @click="editarGrupo(group)" v-if="group.es_admin" style="font-size: 12px; position: absolute; top: -1px; right: 36px; z-index: 2;" class="mb-2 badge bg-primary"><i class="bi bi-pencil-square"></i></small>
                                <h6 class="mb-1">
                                    {{ group.detalle_grupo.nombre }} 
                                    <br>
                                    <small class="text-muted" style="font-size: 10px; font-weight: 900;">
                                        Creado el {{ group.detalle_grupo.fecha }} a las {{ group.detalle_grupo.hora }} 
                                    </small>
                                </h6>
                                <small class="text-muted" style="color: #3253ff !important; font-size: 14px; font-weight: bold;">
                                    Último mensaje
                                </small>
                                <div v-if="group.ultimo_mensaje != null" class="ultimo_mensaje_grupo">
                                    <p class="mb-0" style="font-size: 10px;">
                                        <span style="font-weight: bold; color: #000 !important;">{{ group.ultimo_mensaje.nombre_usuario }}: </span> <p v-html="group.ultimo_mensaje.mensaje" class="mb-1 texto_en_html"></p>   
                                    </p>
                                    <p style="width: 100%; text-align: right; font-size: 9px !important; margin: 0px; margin-top: 3px; margin-bottom: 3px; font-weight: bold;">
                                        {{ group.ultimo_mensaje.fecha }} A las {{ group.ultimo_mensaje.hora }}
                                    </p>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área del chat -->
            <div ref="chatContainer" :class="!esDispositivoMovil ? 'col-8 col-md-9 p-0 h-100' : 'col-12 p-0 h-100'" :style="esDispositivoMovil ? 'display: none;' : 'display: block;'">
                <template v-if="groupSelected">
                    <!-- Encabezado del chat -->
                    <div class="chat-header p-3 border-bottom bg-white" style="background-color: #e1e1e1 !important;">
                        <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <h6 class="mb-0">
                                    {{ groupSelected.detalle_grupo.nombre }} 
                                    <small @click="verParticipantes" style="cursor: pointer;" class="text-muted badge bg-warning text-light">
                                        <i class="bi bi-people-fill"></i>: {{ groupSelected.participantes }}
                                    </small>
                                </h6>
                                <small class="text-muted">
                                    Creado el {{ groupSelected.detalle_grupo.fecha }} a las {{ groupSelected.detalle_grupo.hora }} 
                                </small>                            
                            </div>
                            <div v-if="esDispositivoMovil" style="position: absolute; right: 2vh; top: 3vh;">
                                <button style="width: 35px; font-size: 9px;" class="btn btn-danger me-2" @click="cerrarChat(); ocultarChatContainer()">
                                <i class="bi bi-x-lg text-light"></i>
                                </button>
                            </div>
                            <div v-else class="ms-auto">
                                <button class="btn btn-warning me-2" @click="cerrarChat();">
                                <i class="bi bi-x-lg text-light"></i> Cerrar Chat
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Área de mensajes -->
                    <div 
                        class="chat-messages p-3 overflow-auto"
                        :style="esDispositivoMovil ? 'height: calc(100vh - 20vh); overflow-x: hidden !important;' : 'height: calc(100vh - 118px - ' + altura_editable + 'px);'"
                        ref="messageContainer"
                    >
                        <div 
                            ref="dropGrupos"
                            v-if="isDragging" class="drag-overlay"
                            @dragover.prevent="handleDragOver"
                            @drop="handleDrop"
                        >
                            Suelta los archivos que enviara al grupo aquí 📂
                        </div>
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
                                @dblclick="handleDoubleClick(message)"
                                :id="'mensaje_numero_'+message.id"
                                class="message d-inline-block p-2"
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
                                <div v-else style="position: absolute; left: -25px; top: 0px; z-index: 99;">
                                    <img :src="baseUrl+'images/other.png'" style="width: 30px; height: 50px;">
                                </div>

                                <small :style="message.is_mine ? 'color: white; font-size: 10px;' : 'color: grey; font-size: 10px;'">
                                   {{ message.usuario }}
                                </small>

                                <div @click="scrollToDiv(message.id_mensaje_responde)" v-if="message.id_mensaje_responde != 0" :class="message.is_mine ? 'mensaje-respondiendo' : 'mensaje-respondiendo-other'">
                                    <p :style="message.is_mine ? 'color: white; font-size: 13px; font-style: italic;' : 'color: black; font-size: 13px; font-style: italic;'" class="mb-1 texto_en_html">En respuesta a:</p>
                                    <p :style="message.is_mine ? 'color: white; font-size: 13px;' : 'color: black; font-size: 13px;'" class="mb-1 texto_en_html" v-html="message.mensaje_responde"></p>
                                </div>

                                <!-- Modificar vista previa del archivo -->
                                <div v-if="message.tiene_archivo === 1" class="file-preview mb-2">
                                    <div 
                                        class="d-flex align-items-center p-2 rounded cursor-pointer" 
                                        :class="message.is_mine ? 'bg-white bg-opacity-25' : 'bg-light'"
                                        @click="openFileModal(message.mensaje)"
                                    >
                                        <i class="bi bi-file-earmark me-2" style="font-size: 1.5rem;"></i>
                                        <div class="file-info">
                                        <div :class="message.is_mine ? 'text-white' : 'text-dark'">
                                            {{ message.mensaje }}
                                            <small :class="message.is_mine ? 'text-white-50' : 'text-muted'"> - {{ message.peso }} <br></small>
                                        </div>
                                        <small :class="message.is_mine ? 'text-white-50' : 'text-muted'">Clic para ver</small>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <p :class="message.is_mine ? 'text-white' : 'text-dark'">{{ message.mensaje }}</p>
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
                        <div class="respondiendo" style="position: sticky;bottom: 0px;z-index: 44444;" v-if="estado_respondiendo ==  1">
                            <button class="btn btn-light rounded-circle" style="position: absolute; right: 10px; top: 10px;" @click="estado_respondiendo = 0; message_respondiendo = null;">
                                <i style="color: #5437fd !important;" class="bi bi-x-lg"></i>
                            </button>
                            <p>Respondiendo el mensaje de <span style="font-weight: bold;">{{ message_respondiendo.usuario }}</span></p>
                            <p class="mensaje_respondiendo" v-html="message_respondiendo.mensaje"></p>
                        </div>
                    </div>
                    <!-- Área de entrada de mensaje -->
                    <div v-if="groupSelected?.estado == 1" class="chat-input p-3" style="background-color: #f1ede6 !important;">
                        <div class="input-group" style="align-items: center;">
                        <button style="padding: 0px !important; border-color: #f1ede6 !important; background-color: #f1ede6 !important;" class="btn btn-light" @click="openFileExplorer">
                            <i class="bi bi-plus-lg" style="color: grey; font-size: 1.5rem;"></i>
                        </button>
                        <form 
                            @submit.prevent="guardarMensaje('texto')"
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
                        <form autocomplete="off" style="flex-grow: 1;">
                            <label for="groupName" style="font-weight: bold;" class="form-label">Nombre del Grupo</label>
                            <input type="text" class="form-control" id="groupName" v-model="groupName">
                        </form>
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
      multiple
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


     <!-- Modal Bootstrap para archivos arrastrados-->
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
            isLoading: false,
            esDispositivoMovil: false,
            erroresArchivo: [],
            altura_editable: 78,
            isDragging: false,
            files: [],
            estado_respondiendo: 0,
            message_respondiendo: null,
        }
    },
    async mounted() {
        if(!this.verificarLogin()){
            this.$router.push('/login');
        }else {
            await this.verificarSiEsDispositivoMovil();
            await this.loadGroups();
            await this.verificarGrupoActual();
            this.startAutoUpdate();

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        window.addEventListener("dragenter", this.handleWindowDragEnter);
        window.addEventListener("dragleave", this.handleWindowDragLeave);
        window.addEventListener("dragover", (event) => event.preventDefault());
        window.addEventListener("drop", (event) => {
        this.isDragging = false;
        event.preventDefault(); // Previene la apertura del archivo en otra pestaña
        });
    },
    beforeUnmount() {
        this.stopAutoUpdate();
        window.removeEventListener("dragenter", this.handleWindowDragEnter);
        window.removeEventListener("dragleave", this.handleWindowDragLeave);
        window.removeEventListener("dragover", (event) => event.preventDefault());
        window.removeEventListener("drop", (event) => {
            this.isDragging = false;
            event.preventDefault(); // Previene la apertura del archivo en otra pestaña
        });
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
                this.isLoading = true;
                const maxFileSize = 100 * 1024 * 1024;
                for(var i = 0; i < files.length; i++){
                    if (files[i].size > maxFileSize) {
                        this.erroresArchivo.push('<li style="color: red;">'+ files[i].name + ' es demasiado grande, el tamaño máximo es de 100 MB</li>');
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
                this.$refs.fileInputII.value = '';
                this.estado_respondiendo = 0;
                this.message_respondiendo = null;
                await this.loadMessages();
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },
        async guardarMensajeArchivo(tipo, archivo) {
            const id_mio = localStorage.getItem('id_mio');
            const id_grupo = localStorage.getItem('id_grupo');
            const mensaje = this.newMessage;
            
            try {
                if(this.estado_respondiendo == 0){
                let response = await grupoService.guardarMensaje(id_mio, id_grupo, mensaje, tipo, this.archivo);
                    if (!response.success) {
                        this.erroresArchivo.push('<li style="color: red;">'+response.message+'</li>');
                    }else{
                        this.erroresArchivo.push('<li style="color: green;">'+archivo.name+' subido correctamente</li>');
                    }
                }else{
                    let response = await grupoService.guardarMensajeRespondiendo(id_mio, id_grupo, mensaje, tipo, this.archivo, this.message_respondiendo.usuario, this.message_respondiendo.mensaje, this.message_respondiendo.id);
                    if (!response.success) {
                        this.erroresArchivo.push('<li style="color: red;">'+response.message+'</li>');
                    }else{
                        this.erroresArchivo.push('<li style="color: green;">'+archivo.name+' subido correctamente</li>');
                    }
                }
            } catch (error) {
                this.erroresArchivo.push(error.response.statusText == 'Content Too Large' ? '<li style="color: red;">El archivo '+this.archivo.name+' es demasiado grande</li>' : '<li style="color: red;">'+error.response.statusText+'</li>');
            }
        },
        async guardarMensaje(tipo) {
            const id_mio = localStorage.getItem('id_mio');
            const id_grupo = localStorage.getItem('id_grupo');
            const vacio = this.$refs.editableDiv.textContent.trim();
            if(vacio != ''){
                var mensaje = this.newMessage;

                if(this.estado_respondiendo == 0){
                    let response = await grupoService.guardarMensaje(id_mio, id_grupo, mensaje, tipo, null);
                    if (response.success) {
                        this.newMessage = '';
                        this.$refs.editableDiv.innerHTML = '';
                        this.altura_editable = 78;
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
                }else{
                    let response = await grupoService.guardarMensajeRespondiendo(id_mio, id_grupo, mensaje, tipo, null, this.message_respondiendo.usuario, this.message_respondiendo.mensaje, this.message_respondiendo.id);
                    if (response.success) {
                        this.newMessage = '';
                        this.$refs.editableDiv.innerHTML = '';
                        this.altura_editable = 78;
                        this.estado_respondiendo = 0;
                        this.message_respondiendo = null;
                        await this.loadMessages();
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message
                        });
                    }
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
        editarGrupo(group) {
            Swal.fire({
            title: "cambiar Nombre del grupo",
            input: "text",
            inputValue: group.detalle_grupo.nombre,
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Editar",
            cancelButtonText: "Cancelar",
            showLoaderOnConfirm: true,
            preConfirm: async (nombre) => {
                await grupoService.editarGrupo(group.id_grupo, nombre);
                await this.loadGroups();
                const grupo = this.groups.find(group => group.id_grupo == group.id_grupo);
                this.groupSelected = grupo;
            },
            allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                console.log(result);
            });
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

            if (!this.$refs.dropGrupos || !this.$refs.dropGrupos.contains(event.target)) {
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
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },
        handleDoubleClick(message) {
            this.estado_respondiendo = 1;
            this.message_respondiendo = message;
        },
        async scrollToDiv(id_mensaje) {
            await this.$nextTick(); // Asegurar que el DOM está actualizado
            const element = document.getElementById(`mensaje_numero_${id_mensaje}`);
            console.log(element);
            if (element) {
                element.scrollIntoView({ behavior: "smooth", block: "center" });
            } else {
                console.warn("No se encontró el mensaje con ID:", id);
            }
        }
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
    border: 2px solid rgb(223, 222, 222);
}

.contact-item:hover {
    background-color: #c6e2ff;
}

.contact-item.active {
    background-color: #9fc9f3;
    border: 2px solid rgb(37, 168, 255);
}

/* Estilos para las burbujas de chat */
.message {
    position: relative;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.22) !important;
}

.message-mine {
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

.texto_en_html ::v-deep(p){
  margin-bottom: 0px !important;
}

.message-mine .texto_en_html ::v-deep(span){
  color: white !important;
}

.message-other {
    border-radius: 0px 15px 15px 15px !important;
    background-color: rgb(170, 198, 252);
    border: 1px solid rgba(0, 0, 0, 0.1);
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

.ultimo_mensaje_grupo {
    border-radius: 10px;
    background-color: #fefeff;
    padding: 10px;
    display: flex;
    flex-direction: column;
    margin-top: 6px;
    margin-bottom: 6px;
}

.ultimo_mensaje_grupo p {
    width: 100%;
    overflow: hidden;                 /* Oculta el texto que se desborde */
    text-overflow: ellipsis;
    display: -webkit-box;             /* Define un contenedor flexible */
    -webkit-line-clamp: 2;            /* Limita el contenido a 2 líneas */
    -webkit-box-orient: vertical;     /* Define la orientación vertical */
    line-height: 1.5em;   
}

.ultimo_mensaje_grupo ::v-deep(p), .ultimo_mensaje_grupo ::v-deep(span), .ultimo_mensaje_grupo ::v-deep(strong){
  font-size: 12px !important;
  color: #6c757d !important;
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

.respondiendo{
    margin: 10px;
    border-radius: 10px;
    border-left: 4px solid #674cff;
    padding: 15px;
    background-color: #ecdcff;
    width: 98%;
    overflow-x: hidden;
    color: #5437fd !important;
    box-shadow: 0 0 10px 0 #f9ecff;
    cursor: pointer;
}

.mensaje_respondiendo{
    width: 100%;    
    padding: 10px;        
    border: 1px solid #aa87fd;
    border-radius: 10px;
}

.mensaje-respondiendo {
    margin-top: 10px;
    margin-bottom: 10px;
    border-radius: 10px;
    border-left: 4px solid #674cff;
    padding: 15px;
    background-color: #26074960;
    overflow-x: hidden;
    color: #5437fd !important;
    cursor: pointer;
    transition: all 0.2s ease;  
}

.mensaje-respondiendo:hover{
    transform: scale(1.06);
}

.mensaje-respondiendo-other {
    margin-top: 10px;
    margin-bottom: 10px;
    border-radius: 10px;
    border-left: 4px solid #b5a8ff;
    padding: 15px;
    background-color: #d3acff60;
    overflow-x: hidden;
    color: #5437fd !important;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mensaje-respondiendo-other:hover{
    transform: scale(1.06);
}

</style>
