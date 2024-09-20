document.addEventListener('DOMContentLoaded', function () {
    const bulanNavLeft = document.getElementById('bulan_nav_left');
    const bulanNavRight = document.getElementById('bulan_nav_right');
    const bulanKasReport = document.getElementById('bulankasreport');
    let currentDate = new Date(getUrlParameter('date') || new Date().toISOString().slice(0, 7) + '-01');
    let currentCodeKontrakan = 'all';

    function updateBulanKasReport() {
        const month = currentDate.toLocaleString('default', { month: 'long' });
        const year = currentDate.getFullYear();
        bulanKasReport.textContent = `${month} ${year}`;
    }

    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        const currentUrl = window.location.href;
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('date', formatDate(currentDate));
        const newUrl = `${currentUrl.split('?')[0]}?${urlParams.toString()}`;
        window.history.pushState({}, '', newUrl);
        updateBulanKasReport();
        updateExIn(formatDate(currentDate), currentCodeKontrakan);
        updateBukuKas(formatDate(currentDate), currentCodeKontrakan);
    }

    bulanNavLeft.addEventListener('click', function (event) {
        event.preventDefault();
        changeMonth(-1);
    });

    bulanNavRight.addEventListener('click', function (event) {
        event.preventDefault();
        changeMonth(1);
    });

    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        return `${year}-${month}`;
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
        fetch(`/bulanan/getAllBukuKas?date=${date}&book=${codeKontrakan}`)
            .then(response => response.json())
            .then(data => {
                // const saldoAwalBulan = document.querySelector('#saldo_awal_bulan');
                const semuaPemasukan = document.querySelector('#semua_pemasukan');
                const semuaPengeluaran = document.querySelector('#semua_pengeluaran');
                const akumulasi = document.querySelector('#akumulasi');
                // const saldoAkhirBulan = document.querySelector('#saldo_akhir_bulan');

                // saldoAwalBulan.innerText = `${data.saldoAwalBulan}`;
                semuaPemasukan.innerText = `${data.semuaPemasukan}`;
                semuaPengeluaran.innerText = `${data.semuaPengeluaran}`;
                akumulasi.innerText = `${data.akumulasi}`;
                // saldoAkhirBulan.innerText = `${data.saldoAkhirBulan}`;
            })
            .catch(error => console.error('Error:', error));
    };

    const updateExIn = (date, codeKontrakan = 'all') => {
        fetch(`/bulanan/getAllExIn?date=${date}&book=${codeKontrakan}`)
            .then(response => response.json())
            .then(data => {
                const exinElement = document.querySelector('#ex_exin tbody');
                const inexinElement = document.querySelector('#in_exin tbody');

                exinElement.innerHTML = '';
                inexinElement.innerHTML = '';

                let totalPengeluaran = 0;
                let totalPemasukan = 0;

                // Loop melalui setiap code_kontrakan di dalam data yang diterima
                Object.values(data.data).forEach(transaksi => {
                    // Menambah baris pemasukan untuk setiap kontrakan
                    const pemasukanRow = document.createElement('tr');
                    pemasukanRow.innerHTML = `
                        <td>${transaksi.nama_kontrakan}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.total_masuk.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                    `;
                    inexinElement.appendChild(pemasukanRow);
                    totalPemasukan += transaksi.total_masuk;

                    // Menambah baris pengeluaran untuk setiap kontrakan
                    const pengeluaranRow = document.createElement('tr');
                    pengeluaranRow.innerHTML = `
                        <td>${transaksi.nama_kontrakan}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.total_keluar.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                    `;
                    exinElement.appendChild(pengeluaranRow);
                    totalPengeluaran += transaksi.total_keluar;
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

                // Memperbarui chart dengan data baru (jika ada fungsi chart)
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

    updateBulanKasReport();
    updateExIn(formatDate(currentDate), currentCodeKontrakan);
    updateBukuKas(formatDate(currentDate), currentCodeKontrakan);
});