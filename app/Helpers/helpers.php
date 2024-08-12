<?php

use App\Models\Kontrakan;
use Carbon\Carbon;

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

if (!function_exists('monthName')) {
    function monthName($month)
    {
        $month = bulan(Carbon::parse($month)->month);

        return $month;
    }
}

if (!function_exists('dateIndo')) {
    /**
     * Format dateIndo ke dalam format Indonesia
     *
     * @param string $dateIndo
     * @param bool $cetak_hari
     * @return string
     */
    function dateIndo($tanggal = false)
    {
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

        $text .= "$tanggal $bulan $tahun";

        return $text;
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
    function tanggal($tanggal = false)
    {
        $nama_bulan = array(
            1 => 'JAN',
            'FEB',
            'MAR',
            'APR',
            'MEI',
            'JUN',
            'JUL',
            'AGS',
            'SEP',
            'OKT',
            'NOV',
            'DES'
        );

        $tahun = substr($tanggal, 0, 4);
        $bulan = $nama_bulan[(int)substr($tanggal, 5, 2)];
        $tanggal = substr($tanggal, 8, 2);
        $text = '';

        $text .= "$tanggal-$bulan-$tahun";

        return $text;
    }
}

if (!function_exists('periodeSewa')) {
    /**
     * Format periode sewa ke dalam format Indonesia menggunakan format bulan-tahun
     * contoh: MEI - 21
     *
     * @param string $tanggal_mulai
     * @return string
     */
    function periodeSewa($tanggal_mulai)
    {
        $bulan_mulai = bulan(Carbon::parse($tanggal_mulai)->month);
        $tahun_akhir = tahun(Carbon::parse($tanggal_mulai)->year);

        return $bulan_mulai . ' - ' . $tahun_akhir;
    }
}

if (!function_exists('bulan')) {
    /**
     * Format tanggal ke dalam format Indonesia hanya menampilkan bulan saja
     *
     * @param int $bulan
     * @return string
     */
    function bulan($bulan)
    {
        $nama_bulan = [
            1 => 'JAN',
            'FEB',
            'MAR',
            'APR',
            'MEI',
            'JUN',
            'JUL',
            'AGU',
            'SEP',
            'OKT',
            'NOV',
            'DES'
        ];

        return $nama_bulan[$bulan];
    }
}

if (!function_exists('tahun')) {
    /**
     * Format tanggal ke dalam format Indonesia hanya menampilkan tahun
     *
     * @param string $tanggal
     * @param bool $full optional
     * @return string
     */
    function tahun($tanggal, $full = false)
    {
        $tahun = substr($tanggal, 0, 4);
        if ($full) {
            return $tahun;
        } else {
            return substr($tahun, -2);
        }
    }
}

if (!function_exists('hari')) {
    /**
     * Format tanggal ke dalam format Indonesia hanya menampilkan hari
     *
     * @param string $tanggal
     * @return string
     */
    function hari($tanggal)
    {
        return date('d', strtotime($tanggal));
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
        return number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('nominal')) {
    /**
     * Format angka menjadi format nominal.
     *
     * @param  float|int  $angka
     * @return string
     */
    function nominal($angka)
    {
        return number_format($angka, 0, ',', '.');
    }
}
