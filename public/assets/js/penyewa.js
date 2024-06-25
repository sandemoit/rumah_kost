document.addEventListener('DOMContentLoaded', function() {
    const kontrakanSelect = document.getElementById('id_kontrakan');
    const pilihanKamar = $('#id_kamar');

    // Sembunyikan dropdown pilihan kamar saat halaman dimuat
    pilihanKamar.hide();

    // Menangani perubahan pada dropdown Kontrakan
    kontrakanSelect.addEventListener('change', function() {
        const kontrakanId = this.value;

        // Kirim permintaan Ajax untuk mengambil data kamar berdasarkan kontrakanId
        fetch(`/get-kamar/${kontrakanId}`)
            .then(response => response.json())
            .then(data => {
                // Kosongkan dropdown sebelumnya
                pilihanKamar.empty();

                let allFull = true;

                // Tambahkan opsi kamar baru jika ada data yang diterima
                if (data.length > 0) {
                    data.forEach(kamar => {
                        const option = document.createElement('option');
                        option.value = kamar.id;
                        option.textContent = kamar.nama_kamar;
                        if (kamar.is_full) {
                            option.disabled = true;
                            option.textContent += ' (Terisi)';
                        } else {
                            allFull = false;
                        }
                        pilihanKamar.append(option);
                    });

                    // Jika semua kamar penuh
                    if (allFull) {
                        const option = document.createElement('option');
                        option.textContent = 'Semua Kamar Sudah Penuh';
                        option.disabled = true;
                        option.selected = true;
                        pilihanKamar.append(option);
                    }

                    // Tampilkan pilihan kamar dengan efek slide down
                    pilihanKamar.slideDown();
                } else {
                    // Jika tidak ada data, tambahkan opsi default
                    const option = document.createElement('option');
                    option.textContent = 'Tidak ada kamar tersedia';
                    pilihanKamar.append(option);

                    // Tampilkan pilihan kamar dengan efek slide down
                    pilihanKamar.slideDown();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});