<?php

use App\Models\Kontrakan;

if (!function_exists('getAllKontrakan')) {
    /**
     * Get all kontrakan.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAllKontrakan()
    {
        return Kontrakan::all();
    }
}

if (!function_exists('rupiah')) {
    /**
     * Format angka menjadi format Rupiah.
     *
     * @param  float|int  $angka
     * @return string
     */
    function rupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('tanggal')) {
    /**
     * Format tanggal ke dalam format Indonesia
     *
     * @param string $tanggal
     * @param bool $cetak_hari
     * @return string
     */
    function tanggal($tanggal, $cetak_hari = false)
    {
        $nama_hari = array(
            1 => 'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu'
        );

        $nama_bulan = array(
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );

        $tahun = substr($tanggal, 0, 4);
        $bulan = $nama_bulan[(int)substr($tanggal, 5, 2)];
        $tanggal = substr($tanggal, 8, 2);
        $text = '';

        if ($cetak_hari) {
            $urutan_hari = date('N', strtotime($tanggal));
            $hari = $nama_hari[$urutan_hari];
            $text .= "$hari, $tanggal $bulan $tahun";
        } else {
            $text .= "$tanggal $bulan $tahun";
        }

        return $text;
    }
}
