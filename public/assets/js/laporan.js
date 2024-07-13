function slideOpen(a) {
    $("#trsub_" + a).fadeToggle(300),
    $("#divsub_" + a).slideToggle(300)
}
function slidedetail(a) {
    if ($.trim($("#divsub_" + a).html()).length)
        $("#trsub_" + a).fadeToggle(300);
    else {
        $("#loader_" + a).fadeIn(100);
        var e = $("#detail_report_book").val()
            , t = $("#detail_from").val()
            , n = $("#detail_to").val();
        $.post("laporan/laporan-kirim.php", {
            report_book: e,
            detail_from: t,
            detail_to: n,
            cat_id: a,
            report_detail: global_var
        }, function(e, t) {
            $("#loader_" + a).fadeOut(100);
            var n = e.split("|||");
            $("#divsub_" + a).html(n[1]),
            $("#trsub_" + a).fadeIn()
        })
    }
}

$(document).ready(function() {
    // Inisialisasi datepicker
    $('#lap_tgl').datepicker({
        dateFormat: 'dd-mm-yy',
        onSelect: function(dateText) {
            changedate();
        }
    });

    const updateBukuKas = (selectedDate) => {
        fetch(`/getAllBukuKas?date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                const saldoAwalHari = document.querySelector('#saldo_awal_hari');
                const semuaPemasukan = document.querySelector('#semua_pemasukan');
                const semuaPengeluaran = document.querySelector('#semua_pengeluaran');
                const akumulasi = document.querySelector('#akumulasi');
                const saldoAkhirHari = document.querySelector('#saldo_akhir_hari');
                
                saldoAwalHari.innerText = `${data.saldoAwalHari}`;
                semuaPemasukan.innerText = `${data.semuaPemasukan}`;
                semuaPengeluaran.innerText = `${data.semuaPengeluaran}`;
                akumulasi.innerText = `${data.akumulasi}`;
                saldoAkhirHari.innerText = `${data.saldoAkhirHari}`;
            })
            .catch(error => console.error('Error:', error));
    };

    const changedate = () => {
        const selectedDate = $('#lap_tgl').val();
        updateBukuKas(selectedDate);
    };

    // Inisialisasi awal dengan tanggal hari ini
    const initialDate = $('#lap_tgl').val();
    updateBukuKas(initialDate);
});

document.addEventListener('DOMContentLoaded', function() {
    const updateExIn = (date) => {
        fetch(`/getAllExIn?date=${date}`)
            .then(response => response.json())
            .then(data => {
                const exinElement = document.querySelector('#ex_exin tbody');
                const inexinElement = document.querySelector('#in_exin tbody');

                exinElement.innerHTML = '';
                inexinElement.innerHTML = '';

                let totalPengeluaran = 0;
                let totalPemasukan = 0;

                data.transaksiKeluar.forEach(transaksi => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${transaksi.nama_kontrakan}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                    `;
                    exinElement.appendChild(row);
                    totalPengeluaran += transaksi.nominal;
                });

                data.transaksiMasuk.forEach(transaksi => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${transaksi.nama_kontrakan}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                    `;
                    inexinElement.appendChild(row);
                    totalPemasukan += transaksi.nominal;
                });

                // Menambahkan baris total pengeluaran
                const totalPengeluaranRow = document.createElement('tr');
                totalPengeluaranRow.innerHTML = `
                    <td class="line">&nbsp;</td>
                    <td class="right tdmatauang line">Rp</td>
                    <td class="right tduang line">${totalPengeluaran.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                `;
                exinElement.appendChild(totalPengeluaranRow);

                // Menambahkan baris total pemasukan
                const totalPemasukanRow = document.createElement('tr');
                totalPemasukanRow.innerHTML = `
                    <td class="line">&nbsp;</td>
                    <td class="right tdmatauang line">Rp</td>
                    <td class="right tduang line">${totalPemasukan.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                `;
                inexinElement.appendChild(totalPemasukanRow);

                // Memperbarui chart dengan data baru
                updateChart(totalPemasukan, totalPengeluaran);
            })
            .catch(error => console.error('Error:', error));
    };

    const updateChart = (pemasukan, pengeluaran) => {
        reportChart.data.datasets[0].data = [pemasukan, pengeluaran];
        reportChart.update();
    };

    // Mengambil tanggal dari datepicker dan memperbarui data
    const datePicker = $('#lap_tgl');
    datePicker.datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
    }).on('changeDate', function(e) {
        updateExIn(e.format('yyyy-mm-dd'));
    });

    // Memperbarui data pada saat halaman dimuat
    updateExIn(datePicker.val());

    // Inisialisasi chart
    var ctx = document.getElementById('reportChart').getContext('2d');
    var reportChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                // label: 'Pemasukan vs Pengeluaran',
                data: [0, 0],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false // Sembunyikan legend
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});


