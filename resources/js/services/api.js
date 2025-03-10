import Swal from "sweetalert2";
import {http} from "./http";

export const userService = {
    getUsers: async () => {
        try {
            const response = await http().get('/users');
            return response.data;
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error al obtener usuarios',
                showConfirmButton: false,
                timer: 1500
            });
        }
    },

    logout: async (id) => {
        try {
            const response = await http().get('/logout?id='+id);
            if (response.data.status == "success") {
                localStorage.removeItem('user');
                localStorage.removeItem('id_mio');
                localStorage.removeItem('id_chat');
                localStorage.removeItem('id_grupo');
                window.location.href = '/login';
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error al cerrar sesión',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error al cerrar sesión',
                showConfirmButton: false,
                timer: 1500
            });
        }
    }
}; 


export const chatService = {
    obtenerChatsUsuario: async (id) => {
        try {
            const response = await http().get('/chats-mios?id=' + id);
            return response.data;
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error al obtener chats',
                showConfirmButton: false,
                timer: 1500
            });
        }
    },
    crearChat: async (id_crea, id_amigo) => {
        const response = await http().post('/crear-chat', { id_crea, id_amigo });
        return response.data;
    },
    guardarMensaje: async (id_crea, id_chat, mensaje, tipo, archivo) => {
        const formData = new FormData();
        formData.append('id', id_crea);
        formData.append('chat_id', id_chat);
        formData.append('mensaje', mensaje);
        formData.append('tipo', tipo);
        if (tipo == 'archivo') {
            formData.append('archivo', archivo);
        }

        const headers = {   
            'Content-Type': 'multipart/form-data'
        }

        const response = await http().post('/guardar-mensaje', formData, { headers });
        return response.data;
    },
    obtenerMensajesChat: async (id, chat_id) => {
        try {
            const response = await http().get('/mensajes-chat?id=' + id + '&chat_id=' + chat_id);
            return response.data;
        } catch (error) {
            Swal.fire({ 
                icon: 'error',
                title: 'Oops...',
                text: 'Error al obtener mensajes',
                showConfirmButton: false,
                timer: 1500
            });
        }
    },
    enviarMensajeDifusion: async (id_crea, id_amigos_seleccionados, mensaje, archivo) => {
       const formData = new FormData();
       formData.append('id_crea', id_crea);
       formData.append('id_amigos_seleccionados', id_amigos_seleccionados);
       formData.append('mensaje', mensaje);
       if (archivo) {
        formData.append('archivo', archivo);
       }

       const headers = {
        'Content-Type': 'multipart/form-data'
       }

       const response = await http().post('/enviar-mensaje-difusion', formData, { headers });
       return response.data;
    }
}; 

export const grupoService = {
    crearGrupo: async (id_crea, nombre, id_amigos) => {
        const formData = new FormData();
        formData.append('id_crea', id_crea);
        formData.append('nombre', nombre);
        formData.append('id_amigos', id_amigos);

        const headers = {
            'Content-Type': 'multipart/form-data'
        }
    
        const response = await http().post('/crear-grupo', formData, { headers });
        return response.data;
    },
    obtenerGruposUsuario: async (id) => {
        const response = await http().get('/grupos-mios?id=' + id);
        return response.data;
    },
    guardarMensaje: async (id_crea, id_grupo, mensaje, tipo, archivo) => {
        const formData = new FormData();
        formData.append('id', id_crea);
        formData.append('grupo_id', id_grupo);
        formData.append('mensaje', mensaje);
        formData.append('tipo', tipo);
        if (tipo == 'archivo') {
            formData.append('archivo', archivo);
        }

        const headers = {
            'Content-Type': 'multipart/form-data'
        }

        const response = await http().post('/guardar-mensaje-grupo', formData, { headers });
        return response.data;
    },
    obtenerMensajesGrupo: async (id, grupo_id) => {
        const response = await http().get('/mensajes-grupo?id=' + id + '&grupo_id=' + grupo_id);
        return response.data;
    },
    eliminarParticipante: async (id_crea, id_grupo, id_participante) => {
        const response = await http().post('/eliminar-participante', { id_crea, id_grupo, id_participante });
        return response.data;
    },
    reactivarParticipante: async (id_crea, id_grupo, id_participante) => {
        const response = await http().post('/reactivar-participante', { id_crea, id_grupo, id_participante });
        return response.data;
    },
    agregarParticipantes: async (id_crea, id_grupo, id_participantes) => {
        id_participantes = id_participantes.join(',');
        const response = await http().post('/agregar-participantes', { id_crea, id_grupo, id_participantes });
        return response.data;
    }
}

export const perfilService = {
    actualizarPerfil: async (id, data) => {
        const formData = new FormData();

        formData.append('id', id);
        formData.append('name', data.name);
        formData.append('email', data.email);
        formData.append('empresa', data.empresa);
        formData.append('password', data.password);
        
        const headers = {
            'Content-Type': 'multipart/form-data'
        }

        const response = await http().post('/actualizar-perfil', formData, { headers });
        return response.data;
    },
    misDatos: async (id) => {
        const response = await http().get('/mis-datos?id=' + id);
        return response.data;
    }
}

