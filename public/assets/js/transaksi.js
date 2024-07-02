$(document).ready(function() {
    function catat_out() {
        $('#formPemasukan').slideUp();
        $('#formPengeluaran').slideDown();
        $('#formContainer').slideDown();
    }

    function catat_in() {
        $('#formPengeluaran').slideUp();
        $('#formPemasukan').slideDown();
        $('#formContainer').slideDown();
    }

    // Attach functions to window so they can be called from HTML
    window.catat_out = catat_out;
    window.catat_in = catat_in;
});

$(document).ready(function() {
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

        var formData = {
            tanggalTerima: $('#tanggalTerima').val(),
            kamarPemasukan: $('#kamarPemasukan').val(),
            periodeSewa: $('#periodeSewa').val().replace(/[^0-9]/g, ''),
            tahunSewa: $('#tahunSewa').val(),
            nilaiSewa: $('#nilaiSewa').val(),
            deskripsi: $('#deskripsi').val()
        };

        $.ajax({
            url: '/transaksi-masuk',
            type: 'POST',
            data: formData,
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

        var formData = {
            tanggalPengeluaran: $('#tanggalPengeluaran').val(),
            kamarPengeluaran: $('#kamarPengeluaran').val(),
            nominalPengeluaran: $('#nominalPengeluaran').val(),
            deskripsiPengeluaran: $('#deskripsiPengeluaran').val(),
        };

        $.ajax({
            url: '/transaksi-keluar',
            type: 'POST',
            data: formData,
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
});

$(document).ready(function() {
    // Fungsi untuk mengambil saldo kontrakan berdasarkan code_kontrakan
    function fetchSaldoKontrakan(codeKontrakan) {
        $.ajax({
            url: '/getSaldoKontrakan/' + codeKontrakan,
            type: 'GET',
            success: function(data) {
                $('#saldoKontrakan').text(new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(data.saldo));
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

    function edit_exin(element) {
        var transaksiId = $(element).data('transaksi-id');
        var tipe = $(element).data('tipe');
        var tanggal = $(element).data('tanggal');
        var kamarId = $(element).data('kamar-id');
        var deskripsi = $(element).data('deskripsi');
        var nominal = $(element).data('nominal');

        if (tipe === 'masuk') {
            $('#formPemasukan').slideDown();
            $('#formPengeluaran').slideUp();

            $('#tanggalTerima').val(tanggal);
            $('#kamarPemasukan').val(kamarId).change();
            $('#periodeSewa').val(new Date(tanggal).getMonth() + 1);
            $('[name="tahunSewa"]').val(new Date(tanggal).getFullYear());
            $('#nilaiSewa').val(nominal);
            $('#deskripsi').val(deskripsi);
            $('#deleteButtonPemasukan').removeClass('d-none').attr('href', '/transaksi-masuk/delete/' + transaksiId);
        } else {
            $('#formPengeluaran').slideDown();
            $('#formPemasukan').slideUp();

            $('#tanggalKeluar').val(tanggal);
            $('#kamarPengeluaran').val(kamarId).change();
            $('#deskripsiPengeluaran').val(deskripsi);
            $('#nilaiPengeluaran').val(nominal);
            $('#deleteButtonPengeluaran').removeClass('d-none').attr('href', '/transaksi-keluar/delete/' + transaksiId);
        }
    }

    window.edit_exin = edit_exin;
});
