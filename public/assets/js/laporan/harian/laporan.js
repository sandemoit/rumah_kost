document.addEventListener('DOMContentLoaded', function () {
    {// Inisialisasi datepicker dengan format 'dd-mm-yy'
    $('#lap_tgl').datepicker({
        dateFormat: 'dd-mm-yy', // Set the date format
        onSelect: function (dateText) {
            // Update the input value with the selected date
            $(this).val(dateText);
            // Trigger the onchange event
            const formattedDate = formatDate(dateText);
            const codeKontrakan = selectReport.value;
            const currentUrl = window.location.href;
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('book')) {
                urlParams.set('book', codeKontrakan);
            }
            if (urlParams.has('date')) {
                urlParams.set('date', formattedDate);
            } else {
                urlParams.append('date', formattedDate);
            }
            const newUrl = `${currentUrl.split('?')[0]}?${urlParams.toString()}`;
            window.history.pushState({}, '', newUrl);
            updateBukuKas(formattedDate, codeKontrakan);
            updateExIn(formattedDate, codeKontrakan);
        }
    });

    const selectReport = document.getElementById('selectReport');
    const headingTitle = document.getElementById('headingTitle');
    const cardTitle = document.getElementById('cardTitle');

    selectReport.addEventListener('change', function () {
        const codeKontrakan = this.value;
        const selectedText = this.options[this.selectedIndex].text;

        // Update heading dan nama kartu
        headingTitle.textContent = selectedText;
        cardTitle.textContent = selectedText;

        const date = document.querySelector('.datepicker').value;
        const formattedDate = formatDate(date);
        window.history.pushState({}, '', `?book=${codeKontrakan}`);
        updateBukuKas(formattedDate, codeKontrakan);
        updateExIn(formattedDate, codeKontrakan);
    });

    const updateBukuKas = (date, codeKontrakan = 'all') => {
        fetch(`/getAllBukuKas?date=${date}&book=${codeKontrakan}`)
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

    const updateExIn = (date, codeKontrakan = 'all') => {
        fetch(`/getAllExIn?date=${date}&book=${codeKontrakan}`)
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

    const formatDate = (date) => {
        if (typeof date !== 'string') {
            return '';
        }
        const [day, month, year] = date.split('-');
        return `${day}-${month}-${year}`;
    };

    const changedate = () => {
        const date = document.querySelector('.datepicker').value;
        const codeKontrakan = document.getElementById('selectReport').value;
        const formattedDate = formatDate(date);
        updateBukuKas(formattedDate, codeKontrakan);
        updateExIn(formattedDate, codeKontrakan);
    };

    // Inisialisasi chart
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

    const todayDate = document.querySelector('.datepicker').value;
    updateBukuKas(todayDate);
    updateExIn(todayDate);}
});

