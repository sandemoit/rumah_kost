document.addEventListener('DOMContentLoaded', function () {
    const tahunNavLeft = document.getElementById('tahun_nav_left');
    const tahunNavRight = document.getElementById('tahun_nav_right');
    const tahunKasReport = document.getElementById('tahunkasreport');
    let currentDate = new Date();
    let currentCodeKontrakan = 'all';

    function updateTahunReport() {
        const year = currentDate.getFullYear();
        tahunKasReport.textContent = `${year}`;
    }

    function changeYear(delta) {
        currentDate.setFullYear(currentDate.getFullYear() + delta);
        updateTahunReport();
        updateExIn(formatDate(currentDate), currentCodeKontrakan);
        updateBukuKas(formatDate(currentDate), currentCodeKontrakan);
    }

    tahunNavLeft.addEventListener('click', function (event) {
        event.preventDefault();
        changeYear(-1);
    });

    tahunNavRight.addEventListener('click', function (event) {
        event.preventDefault();
        changeYear(1);
    });

    function formatDate(date) {
        const year = date.getFullYear();
        return `${year}`;
    }

    const selectReport = document.getElementById('selectReport');
    const headingTitle = document.getElementById('headingTitle');
    const cardTitle = document.getElementById('cardTitle');

    selectReport.addEventListener('change', function () {
        currentCodeKontrakan = this.value;
        const selectedText = this.options[this.selectedIndex].text;

        // Update heading dan nama kartu
        headingTitle.textContent = selectedText;
        cardTitle.textContent = selectedText;

        const formattedDate = formatDate(currentDate);
        window.history.pushState({}, '', `?book=${currentCodeKontrakan}`);
        updateBukuKas(formattedDate, currentCodeKontrakan);
        updateExIn(formattedDate, currentCodeKontrakan);
    });

    const updateBukuKas = (date, codeKontrakan = 'all') => {
        fetch(`/tahunan/getAllBukuKas?date=${date}&book=${codeKontrakan}`)
            .then(response => response.json())
            .then(data => {
                // const saldoAwalTahun = document.querySelector('#saldo_awal_tahun');
                const semuaPemasukan = document.querySelector('#semua_pemasukan');
                const semuaPengeluaran = document.querySelector('#semua_pengeluaran');
                const akumulasi = document.querySelector('#akumulasi');
                // const saldoAkhirTahun = document.querySelector('#saldo_akhir_tahun');

                // saldoAwalTahun.innerText = `${data.saldoAwalTahun}`;
                semuaPemasukan.innerText = `${data.semuaPemasukan}`;
                semuaPengeluaran.innerText = `${data.semuaPengeluaran}`;
                akumulasi.innerText = `${data.akumulasi}`;
                // saldoAkhirTahun.innerText = `${data.saldoAkhirTahun}`;
            })
            .catch(error => console.error('Error:', error));
    };

     const updateExIn = (date, codeKontrakan = 'all') => {
        fetch(`/tahunan/getAllExIn?date=${date}&book=${codeKontrakan}`)
            .then(response => response.json())
            .then(data => {
                const exinElement = document.querySelector('#ex_exin tbody');
                const inexinElement = document.querySelector('#in_exin tbody');

                exinElement.innerHTML = '';
                inexinElement.innerHTML = '';

                let totalPengeluaran = 0;
                let totalPemasukan = 0;
                
                const kamarMap = {}; // Object untuk menyimpan total nominal per kamar

                data.transaksiKeluar.forEach(transaksi => {
                    if (Array.isArray(transaksi.nama_kamar)) {
                        transaksi.nama_kamar.forEach(kamar => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${kamar}</td>
                                <td class="right tdmatauang">Rp</td>
                                <td class="right tduang">${parseFloat(transaksi.total_nominal)}</td>`;
                            exinElement.appendChild(row);
                        });
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${transaksi.nama_kamar}</td>
                            <td class="right tdmatauang">Rp</td>
                            <td class="right tduang">${parseFloat(transaksi.total_nominal)}</td>`;
                        exinElement.appendChild(row);
                    }
                    totalPengeluaran += parseFloat(transaksi.total_nominal);
                });

                // Setelah data tergabung, render ke dalam tabel
                Object.keys(kamarMap).forEach(kamar => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${kamar}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${kamarMap[kamar].toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>`;
                    exinElement.appendChild(row);
                });

                data.transaksiMasuk.forEach(transaksi => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${transaksi.nama_kamar}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.total_nominal.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                    `;
                    inexinElement.appendChild(row);
                    
                    // Pastikan total_nominal diubah menjadi number sebelum dijumlahkan
                    totalPemasukan += parseFloat(transaksi.total_nominal);
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

    var ctx = document.getElementById('reportChart').getContext('2d');
    var reportChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                data: [0, 0],
                backgroundColor: [
                    'rgba(0, 128, 0, 0.2)',
                    'rgba(255, 0, 0, 0.2)'
                ],
                borderColor: [
                    'rgba(0, 128, 0, 1)',
                    'rgba(255, 0, 0, 1)'
                ],
                borderWidth: 2
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

    updateTahunReport();
    updateExIn(formatDate(currentDate), currentCodeKontrakan);
    updateBukuKas(formatDate(currentDate), currentCodeKontrakan);
});