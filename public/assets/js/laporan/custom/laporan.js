document.addEventListener('DOMContentLoaded', function () {
    const selectReport = document.getElementById('selectReport');
    const headingTitle = document.getElementById('headingTitle');
    const cardTitle = document.getElementById('cardTitle');

     // Inisialisasi Date Range Picker
    $('#daterange').daterangepicker({
        locale: {
            format: 'MM/DD/YYYY' // Sesuaikan format
        }
    }, function(start, end) {
        // Ketika tanggal dipilih, format dan kirim data
        const dr = `${start.format('MM/DD/YYYY')} - ${end.format('MM/DD/YYYY')}`;
        $('#daterange').val(dr);

        // Memperbarui URL dan memicu pembaruan data
        const ck = selectReport.value;
        const cu = window.location.href;
        const up = new URLSearchParams(window.location.search);

        if (up.has('book')) {
            up.set('book', ck);
        }
        up.set('date', dr); // Set rentang tanggal baru

        const newUrl = `${cu.split('?')[0]}?${up.toString()}`;
        window.history.pushState({}, '', newUrl);
        
        updateBukuKas(dr, ck);
        updateExIn(dr, ck);
    });

    // Update ketika report dipilih
    selectReport.addEventListener('change', function () {
        const codeKontrakan = this.value;
        const selectedText = this.options[this.selectedIndex].text;

        // Update heading dan nama kartu
        headingTitle.textContent = selectedText;
        cardTitle.textContent = selectedText;

        const date = $('#daterange').val();
        const formattedDate = date;
        window.history.pushState({}, '', `?book=${codeKontrakan}`);
        updateBukuKas(formattedDate, codeKontrakan);
        updateExIn(formattedDate, codeKontrakan);
    });

     // Fungsi untuk memformat tanggal
    const formatDate = (date) => {
        if (typeof date !== 'string') {
            return '';
        }
        const [month, day, year] = date.split('/');
        return `${day}/${month}/${year}`; // Format: dd-mm-yyyy
    };

    // Fungsi untuk memperbarui buku kas
    const updateBukuKas = (date, codeKontrakan = 'all') => {
        fetch(`/custom/getAllBukuKas?date=${date}&book=${codeKontrakan}`)
            .then(response => response.json())
            .then(data => {
                document.querySelector('#semua_pemasukan').innerText = `${data.semuaPemasukan}`;
                document.querySelector('#semua_pengeluaran').innerText = `${data.semuaPengeluaran}`;
                document.querySelector('#akumulasi').innerText = `${data.akumulasi}`;
            })
            .catch(error => console.error('Error:', error));
    };

    // Fungsi untuk memperbarui pemasukan dan pengeluaran
    const updateExIn = (date, codeKontrakan = 'all') => {
        fetch(`/custom/getAllExIn?date=${date}&book=${codeKontrakan}`)
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
                        <td>${transaksi.nama_kamar}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>`;
                    exinElement.appendChild(row);
                    totalPengeluaran += transaksi.nominal;
                });

                data.transaksiMasuk.forEach(transaksi => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${transaksi.nama_kamar}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>`;
                    inexinElement.appendChild(row);
                    totalPemasukan += transaksi.nominal;
                });

                // Total Pengeluaran
                const totalPengeluaranRow = document.createElement('tr');
                totalPengeluaranRow.innerHTML = `
                    <td class="line">&nbsp;</td>
                    <td class="right tdmatauang line">Rp</td>
                    <td class="right tduang line">${totalPengeluaran.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>`;
                exinElement.appendChild(totalPengeluaranRow);

                // Total Pemasukan
                const totalPemasukanRow = document.createElement('tr');
                totalPemasukanRow.innerHTML = `
                    <td class="line">&nbsp;</td>
                    <td class="right tdmatauang line">Rp</td>
                    <td class="right tduang line">${totalPemasukan.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>`;
                inexinElement.appendChild(totalPemasukanRow);

                // Update chart
                updateChart(totalPemasukan, totalPengeluaran);
            })
            .catch(error => console.error('Error:', error));
    };

     // Fungsi untuk memperbarui chart
    const updateChart = (pemasukan, pengeluaran) => {
        reportChart.data.datasets[0].data = [pemasukan, pengeluaran];
        reportChart.update();
    };

    // Inisialisasi chart
    const ctx = document.getElementById('reportChart').getContext('2d');
    const reportChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['rgba(0, 128, 0, 0.2)', 'rgba(255, 0, 0, 0.2)'],
                borderColor: ['rgba(0, 128, 0, 1)', 'rgba(255, 0, 0, 1)'],
                borderWidth: 2
            }]
        },
        options: {
            plugins: {
                legend: { display: false } // Sembunyikan legend
            },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Mengambil tanggal default pada saat halaman dimuat pertama kali
    const todayDate = $('#daterange').val();
    updateBukuKas(todayDate);
    updateExIn(todayDate);
});

