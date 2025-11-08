import axios from 'axios';
import Swal from 'sweetalert2';

window.axios = axios;
window.Swal = Swal;

// Add a simple toast function using SweetAlert2
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
