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
    }
}; 