// sweet alert
document.addEventListener('DOMContentLoaded', function () {
    if (typeof failed !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            customClass: {
                popup: 'colored-toast',
            },
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        // Panggil Toast.fire() untuk menampilkan pesan toast
        Toast.fire({
            icon: 'error',
            title: failed,
        })
    } else if (typeof success !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            customClass: {
                popup: 'colored-toast',
            },
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        // Panggil Toast.fire() untuk menampilkan pesan toast
        Toast.fire({
            icon: 'success',
            title: success,
        })
    }
});