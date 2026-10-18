import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (el) => {
        el.addEventListener('mouseenter', Swal.stopTimer);
        el.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

window.Swal = Swal;

window.appToast = (icon, title) => Toast.fire({ icon, title });

window.appConfirm = (options = {}) => Swal.fire({
    icon: 'warning',
    title: 'Yakin?',
    text: 'Tindakan ini tidak dapat dibatalkan.',
    showCancelButton: true,
    confirmButtonText: 'Ya, lanjutkan',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#2F6FED',
    cancelButtonColor: '#64748B',
    reverseButtons: true,
    ...options,
});

// Normalizes the { success, data, message } API envelope into a toast.
window.handleAjaxError = (xhr) => {
    const res = xhr.responseJSON;

    if (xhr.status === 422 && res?.errors) {
        const firstError = Object.values(res.errors)[0][0];
        window.appToast('error', firstError);
        return;
    }

    window.appToast('error', res?.message || 'Terjadi kesalahan. Silakan coba lagi.');
};

export default Toast;
