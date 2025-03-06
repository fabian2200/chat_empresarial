import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

export const showSuccessMessage = (title, message) => {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#1a237e',
        customClass: {
            confirmButton: 'btn btn-primary'
        }
    });
};

export const showErrorMessage = (title, message) => {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#1a237e',
        customClass: {
            confirmButton: 'btn btn-primary'
        }
    });
};

export const showToast = (icon, message) => {
    Toast.fire({
        icon: icon,
        title: message
    });
};

export default Swal; 