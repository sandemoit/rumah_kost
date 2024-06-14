// public/js/deleteUser.js

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete').forEach(function(element) {
        element.addEventListener('click', function(event) {
            confirmDelete(event, element);
        });
    });
});

function confirmDelete(event, element) {
    event.preventDefault();
    const url = element.getAttribute('href');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Anda tidak akan bisa mengembalikannya!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hapus!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Terhapus!',
                            data.success,
                            'success'
                        ).then(() => {
                            location.reload(); // Reload halaman setelah penghapusan
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            data.error,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menghapus user.',
                        'error'
                    );
                });
        }
    });
}
