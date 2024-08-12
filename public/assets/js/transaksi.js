function submitFilter() {
    const form = document.getElementById('filterForm');
    form.submit();
}

// slide down and up form input trx
$(document).ready(function() {
    function resetForm() {
        // Reset form pemasukan
        $('#transaksiId').val('');
        $('#tanggalTunggakan').val(new Date().toISOString().split('T')[0]);
        $('#tanggalTerima').val(new Date().toISOString().split('T')[0]);
        $('#kamarPemasukan').val('').change();
        $('#periodeSewa').val('');
        $('#periodeDeskripsi').val('');
        $('#nilaiSewa').val('');
        $('#deskripsi').val('');
        $('#codeKontrakan').val('');

        // Reset form pengeluaran
        $('#tanggalPengeluaran').val(new Date().toISOString().split('T')[0]);
        $('#tanggalPengeluaran').val(new Date().toISOString().split('T')[0]);
        $('#kamarPengeluaran').val('').change();
        $('#deskripsiPengeluaran').val('');
        $('#nominalPengeluaran').val('');
        $('#codeKontrakanKeluar').val('');

        // Hide delete buttons
        $('#deleteButtonPemasukan').addClass('d-none').attr('href', 'javascript:void(0)');
        $('#deleteButtonPengeluaran').addClass('d-none').attr('href', 'javascript:void(0)');
    }

    function catat_tunggakan() {
        $('#formTunggakan').slideDown();
        $('#formPengeluaran').slideUp();
        $('#formPemasukan').slideUp();
        $('#formContainer').slideDown();
    }

    function catat_out() {
        resetForm();
        $('#formPemasukan').slideUp();
        $('#formTunggakan').slideUp();
        $('#formPengeluaran').slideDown();
        $('#formContainer').slideDown();
    }

    function catat_in() {
        resetForm();
        $('#formPengeluaran').slideUp();
        $('#formTunggakan').slideUp();
        $('#formPemasukan').slideDown();
        $('#formContainer').slideDown();
    }

    // Attach functions to window so they can be called from HTML
    window.catat_out = catat_out;
    window.catat_in = catat_in;
    window.catat_tunggakan = catat_tunggakan;
});

// form pemasukan dan pengeluaran
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
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    $('#kamarTunggakan').change(function() {
        var kamarId = $(this).val();
        if (kamarId) {
            $.ajax({
                url: '/getTunggakan/' + kamarId,
                type: 'GET',
                success: function(data) {
                    $('#periodeTunggakanDeskripsi').val(data.periodeTunggakanDeskripsi);
                    $('#nilaiTunggakan').val(data.nilaiTunggakan);
                    $('#periodeTunggakan').val(data.periodeTunggakan);
                    $('#codeKontrakan').val(data.codeKontrakan);
                }
            });
        }
    });

    $('#kamarPemasukan').change(function() {
        var kamarId = $(this).val();
        if (kamarId) {
            $.ajax({
                url: '/getKamarData/' + kamarId,
                type: 'GET',
                success: function(data) {
                    $('#periodeSewa').val(data.periodeSewa);
                    $('#nilaiSewa').val(data.nilaiSewa);
                    $('#periodeDeskripsi').val(data.periodeDeskripsi);
                    $('#codeKontrakan').val(data.codeKontrakan);
                }
            });
        }
    });

    $('#pengeluaran').click(function() {
        var url = new URL(window.location.href);
        var codeKontrakan = url.pathname.split('/')[2];
        $('#codeKontrakanKeluar').val(codeKontrakan);
    });

    $('#formTunggakan').submit(function(event) {
        event.preventDefault();

        var data = {
            tanggalTerima: $('#tanggalTerima').val(),
            kamarPemasukan: $('#kamarTunggakan').val(),
            periodeSewa: $('#periodeTunggakan').val(),
            deskripsi: $('#periodeTunggakanDeskripsi').val(),
            nilaiSewa: $('#nilaiTunggakan').val(),
            codeKontrakan: $('#codeKontrakan').val(),
        };

        $.ajax({
            url: '/transaksi-masuk',
            type: 'POST',
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
                    }, 1500)
                }
            },
            error: function(response) {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });

    $('#formPemasukan').submit(function(event) {
        event.preventDefault();

        var data = {
            transaksiId: $('#transaksiId').val(),
            tanggalTerima: $('#tanggalTerima').val(),
            kamarPemasukan: $('#kamarPemasukan').val(),
            periodeSewa: $('#periodeSewa').val(),
            deskripsi: $('#periodeDeskripsi').val(),
            nilaiSewa: $('#nilaiSewa').val(),
            codeKontrakan: $('#codeKontrakan').val(),
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
                    }, 1500)
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
            codeKontrakanKeluar: $('#codeKontrakanKeluar').val(),
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
                    }, 1500)
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
                $('#saldoKontrakan').text(`Rp ${saldo}`);
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
    var periodeSewa = $(element).data('periode-sewa');

    if (tipe === 'masuk') {
        $('#formPengeluaran').slideUp();
        $('#formTunggakan').slideUp();
        $('#formPemasukan').slideDown();
        $('#formContainer').slideDown();

        $('#transaksiId').val(transaksiId);
        $('#tanggalTerima').val(tanggal);
        $('#kamarPemasukan').val(kamarId);
        $('#periodeSewa').val(periodeSewa);
        $('#periodeDeskripsi').val(deskripsi);
        $('#nilaiSewa').val(nominal);

        // $('#deleteButtonPemasukan')
        //     .removeClass('d-none')
        //     .attr('href', '/transaksi-masuk/delete/' + transaksiId)
        //     .attr('onclick', 'confirmDelete(event, this)');
    } else {
        $('#formPemasukan').slideUp();
        $('#formTunggakan').slideUp();
        $('#formPengeluaran').slideDown();
        $('#formContainer').slideDown();

        $('#transaksiId').val(transaksiId);
        $('#tanggalPengeluaran').val(tanggal);
        $('#kamarPengeluaran').val(kamarId).change();
        $('#deskripsiPengeluaran').val(deskripsi);
        $('#nominalPengeluaran').val(nominal);
        
        // $('#deleteButtonPengeluaran')
        //     .removeClass('d-none')
        //     .attr('href', '/transaksi-keluar/delete/' + transaksiId)
        //     .attr('onclick', 'confirmDelete(event, this)');
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

document.getElementById('bulan_nav_left').addEventListener('click', function(event) {
    event.preventDefault();
    changeMonth(-1);
});

document.getElementById('bulan_nav_right').addEventListener('click', function(event) {
    event.preventDefault();
    changeMonth(1);
});

function changeMonth(offset) {
    const monthSelect = document.getElementById('nav_month');
    const yearSelect = document.getElementById('nav_year');
    let month = parseInt(monthSelect.value);
    let year = parseInt(yearSelect.value);

    month += offset;

    if (month < 1) {
        month = 12;
        year -= 1;
    } else if (month > 12) {
        month = 1;
        year += 1;
    }

    monthSelect.value = month;
    yearSelect.value = year;

    document.getElementById('filterForm').submit();
}

document.addEventListener("DOMContentLoaded", function() {
    // Lakukan fetch ke controller untuk mendapatkan total saldo
    fetch('/route-to-getAllSaldo')
        .then(response => response.json())
        .then(data => {
            // Format saldo menjadi rupiah dan tampilkan
            const formattedSaldo = formatRupiah(data.totalSaldo);
            document.getElementById('totalSaldo').innerText = formattedSaldo;
        })
        .catch(error => {
            console.error('Error fetching total saldo:', error);
            document.getElementById('totalSaldo').innerText = "Error";
        });
});

// Fungsi untuk memformat angka ke dalam format rupiah
function formatRupiah(angka) {
    var number_string = angka.toString(),
        sisa = number_string.length % 3,
        rupiah = number_string.substr(0, sisa),
        ribuan = number_string.substr(sisa).match(/\d{3}/g);

    if (ribuan) {
        var separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    return 'Rp ' + rupiah;
}