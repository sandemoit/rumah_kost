function submitFilter() {
    const form = document.getElementById('filterForm');
    form.submit();
}

$(document).ready(function() {
    function resetForm() {
        // Reset form pemasukan
        $('#transaksiId').val('');
        $('#saldo').val('');
        $('#tanggalTerima').val(new Date().toISOString().split('T')[0]);
        $('#kamarPemasukan').val('').change();
        $('#periodeSewa').val('');
        $('#tahunSewa').val(new Date().getFullYear());
        $('#nilaiSewa').val('');
        $('#deskripsi').val('');

        // Reset form pengeluaran
        $('#tanggalPengeluaran').val(new Date().toISOString().split('T')[0]);
        $('#kamarPengeluaran').val('').change();
        $('#deskripsiPengeluaran').val('');
        $('#nominalPengeluaran').val('');

        // Hide delete buttons
        $('#deleteButtonPemasukan').addClass('d-none').attr('href', '#');
        $('#deleteButtonPengeluaran').addClass('d-none').attr('href', '#');
    }

    function catat_out() {
        resetForm();
        $('#formPemasukan').slideUp();
        $('#formPengeluaran').slideDown();
        $('#formContainer').slideDown();
    }

    function catat_in() {
        resetForm();
        $('#formPengeluaran').slideUp();
        $('#formPemasukan').slideDown();
        $('#formContainer').slideDown();
    }

    // Attach functions to window so they can be called from HTML
    window.catat_out = catat_out;
    window.catat_in = catat_in;
});


$(document).ready(function() {
    $( '.multiple' ).select2( {
        theme: "bootstrap-5",
        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        placeholder: $( this ).data( 'placeholder' ),
        closeOnSelect: false,
    } );

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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

    $('#kamarPemasukan').change(function() {
        var kamarId = $(this).val();
        if (kamarId) {
            $.ajax({
                url: '/getKamarData/' + kamarId,
                type: 'GET',
                success: function(data) {
                    $('#periodeSewa').val(data.periodeSewa);
                    $('#nilaiSewa').val(data.nilaiSewa);
                }
            });
        }
    });

    $('#formPemasukan').submit(function(event) {
        event.preventDefault();

        var data = {
            transaksiId: $('#transaksiId').val(),
            tanggalTerima: $('#tanggalTerima').val(),
            kamarPemasukan: $('#kamarPemasukan').val(),
            periodeSewa: $('#periodeSewa').val().replace(/[^0-9]/g, ''),
            tahunSewa: $('#tahunSewa').val(),
            nilaiSewa: $('#nilaiSewa').val(),
            deskripsi: $('#deskripsi').val()
        };

        $.ajax({
            url: (data.transaksiId) ? '/transaksi-masuk/update/' + data.transaksiId : '/transaksi-masuk',
            type: (data.transaksiId) ? 'PUT' : 'POST',
            data: data,
            success: function(response) {
                // Panggil Toast.fire() untuk menampilkan pesan toast
                Toast.fire({
                    icon: response.status,
                    title: response.message,
                })

                // Setelah 3 detik, reload halaman jika status bukan error
                if (response.status !== 'error') {
                    setTimeout(function() {
                        window.location.reload();
                    }, 3000)
                }
            },
            error: function(response) {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });

    $('#formPengeluaran').submit(function(event) {
        event.preventDefault();

        var data = {
            transaksiId: $('#transaksiId').val(),
            tanggalPengeluaran: $('#tanggalPengeluaran').val(),
            kamarPengeluaran: $('#kamarPengeluaran').val(),
            nominalPengeluaran: $('#nominalPengeluaran').val(),
            deskripsiPengeluaran: $('#deskripsiPengeluaran').val(),
        };

        $.ajax({
            url: (data.transaksiId) ? '/transaksi-keluar/update/' + data.transaksiId : '/transaksi-keluar',
            type: (data.transaksiId) ? 'PUT' : 'POST',
            data: data,
            success: function(response) {
                 // Panggil Toast.fire() untuk menampilkan pesan toast
                Toast.fire({
                    icon: response.status,
                    title: response.message,
                })

                // Setelah 3 detik, reload halaman jika status bukan error
                if (response.status !== 'error') {
                    setTimeout(function() {
                        window.location.reload();
                    }, 3000)
                }
            },
            error: function(response) {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });

    // Fungsi untuk mengambil saldo kontrakan berdasarkan code_kontrakan
    function fetchSaldoKontrakan(codeKontrakan) {
        $.ajax({
            url: '/getSaldoKontrakan/' + codeKontrakan,
            type: 'GET',
            success: function(data) {
                var saldo = data.saldo || 0; // Jika saldo null atau undefined, set ke 0
                $('#saldoKontrakan').text(new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(saldo));
            },
            error: function(error) {
                console.error('Error fetching saldo:', error);
                $('#saldoKontrakan').text('Error');
            }
        });
    }

    // Mengambil code_kontrakan dari segment URL
    var codeKontrakan = window.location.pathname.split('/')[2];
    fetchSaldoKontrakan(codeKontrakan);
});

function edit_exin(element) {
    var transaksiId = $(element).data('transaksi-id');
    var tipe = $(element).data('tipe');
    var tanggal = $(element).data('tanggal');
    var kamarId = $(element).data('kamar-id');
    var deskripsi = $(element).data('deskripsi');
    var nominal = $(element).data('nominal');
    var saldo = $(element).data('saldo');

    if (tipe === 'masuk') {
        $('#formPengeluaran').slideUp();
        $('#formPemasukan').slideDown();
        $('#formContainer').slideDown();

        $('#transaksiId').val(transaksiId);
        $('#saldo').val(saldo);
        $('#tanggalTerima').val(tanggal);
        $('#kamarPemasukan').val(kamarId).change();
        $('#periodeSewa').val(new Date(tanggal).getMonth() + 1);
        $('#tahunSewa').val(new Date(tanggal).getFullYear());
        $('#nilaiSewa').val(nominal);
        $('#deskripsi').val(deskripsi);

        $('#deleteButtonPemasukan')
            .removeClass('d-none')
            .attr('href', '/transaksi-masuk/delete/' + transaksiId)
            .attr('onclick', 'confirmDelete(event, this)');
    } else {
        $('#formPemasukan').slideUp();
        $('#formPengeluaran').slideDown();
        $('#formContainer').slideDown();

        $('#transaksiId').val(transaksiId);
        $('#saldo').val(saldo);
        $('#tanggalPengeluaran').val(tanggal);
        $('#kamarPengeluaran').val(kamarId).change();
        $('#deskripsiPengeluaran').val(deskripsi);
        $('#nominalPengeluaran').val(nominal);
        
        $('#deleteButtonPengeluaran')
            .removeClass('d-none')
            .attr('href', '/transaksi-keluar/delete/' + transaksiId)
            .attr('onclick', 'confirmDelete(event, this)');
    }
}

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
                    if (data.status === 'success') {
                        Swal.fire(
                            'Terhapus!',
                            data.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            data.message,   
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan pada server.',
                        'error'
                    );
                });
        }
    });
}

$(document).ready(function() {
    window.edit_exin = edit_exin;
    window.confirmDelete = confirmDelete;
});