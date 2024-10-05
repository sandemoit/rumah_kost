document.addEventListener('DOMContentLoaded', function () {
    {
    // Inisialisasi datepicker dengan format 'dd-mm-yy'
    $('#daterange').daterangepicker({
        dateFormat: 'MM/DD/YYYY', // Set the date format
        onSelect: function (dateText) {
            // Update the input value with the selected date
            $(this).val(dateText);
            // Trigger the onchange event
            const fd = formatDate(dateText);
            const ck = selectReport.value;
            const cu = window.location.href;
            const up = new URLSearchParams(window.location.search);
            if (up.has('book')) {
                up.set('book', ck);
            }
            if (up.has('date')) {
                up.set('date', fd);
            } else {
                up.append('date', fd);
            }
            const newUrl = `${cu.split('?')[0]}?${up.toString()}`;
            window.history.pushState({}, '', newUrl);
            updateBukuKas(fd, ck);
            updateExIn(fd, ck);
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

        const date = $('#daterange').val();
        const formattedDate = formatDate(date);
        window.history.pushState({}, '', `?book=${codeKontrakan}`);
        updateBukuKas(formattedDate, codeKontrakan);
        updateExIn(formattedDate, codeKontrakan);
    });

    const updateBukuKas = (date, codeKontrakan = 'all') => {
        fetch(`/custom/getAllBukuKas?date=${date}&book=${codeKontrakan}`)
            .then(response => response.json())
            .then(data => {
                // const saldoAwalCustom = document.querySelector('#saldo_awal_custom');
                const semuaPemasukan = document.querySelector('#semua_pemasukan');
                const semuaPengeluaran = document.querySelector('#semua_pengeluaran');
                const akumulasi = document.querySelector('#akumulasi');
                // const saldoAkhirCustom = document.querySelector('#saldo_akhir_custom');

                // saldoAwalCustom.innerText = `${data.saldoAwalCustom}`;
                semuaPemasukan.innerText = `${data.semuaPemasukan}`;
                semuaPengeluaran.innerText = `${data.semuaPengeluaran}`;
                akumulasi.innerText = `${data.akumulasi}`;
                // saldoAkhirCustom.innerText = `${data.saldoAkhirCustom}`;
            })
            .catch(error => console.error('Error:', error));
    };

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
                    if (Array.isArray(transaksi.nama_kamar)) {
                        transaksi.nama_kamar.forEach(kamar => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${kamar}</td>
                                <td class="right tdmatauang">Rp</td>
                                <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>`;
                            exinElement.appendChild(row);
                        });
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${transaksi.nama_kamar}</td>
                            <td class="right tdmatauang">Rp</td>
                            <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>`;
                        exinElement.appendChild(row);
                    }
                    totalPengeluaran += transaksi.nominal;
                });

                // Loop melalui setiap code_kontrakan di dalam data yang diterima
                data.transaksiMasuk.forEach(transaksi => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${transaksi.nama_kamar}</td>
                        <td class="right tdmatauang">Rp</td>
                        <td class="right tduang">${transaksi.nominal.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>
                    `;
                    inexinElement.appendChild(row);
                    totalPemasukan += transaksi.nominal;
                });

                // Menambahkan baris total pengeluaran
                const totalPengeluaranRow = document.createElement('tr');
                totalPengeluaranRow.innerHTML = `
                    <td class="line">&nbsp;</td>
                    <td class="right tdmatauang line">Rp</td>
                    <td class="right tduang line">${totalPengeluaran.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>
                `;
                exinElement.appendChild(totalPengeluaranRow);

                // Menambahkan baris total pemasukan
                const totalPemasukanRow = document.createElement('tr');
                totalPemasukanRow.innerHTML = `
                    <td class="line">&nbsp;</td>
                    <td class="right tdmatauang line">Rp</td>
                    <td class="right tduang line">${totalPemasukan.toLocaleString('id-ID', { minimumFractionDigits: 0 })}</td>
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
        const [month, day, year] = date.split('/');
        return `${day}-${month}-${year}`;
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

    const todayDate = $('#daterange').val();
   
    updateBukuKas(todayDate);
    updateExIn(todayDate);}
});

