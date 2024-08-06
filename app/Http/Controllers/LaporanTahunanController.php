<?php

namespace App\Http\Controllers;

use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTahunanController extends Controller
{
    public function tahunan()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.umum', $data);
    }

    public function umum()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan Tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y');
        } else {
            try {
                // Ubah format tanggal dari 'Y' ke 'Y'
                $date = Carbon::createFromFormat('Y', $date)->format('Y');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y format.'], 400);
            }
        }

        // Pisahkan tahun dari parameter 'date'
        $year = Carbon::createFromFormat('Y', $date)->year;

        // Menghitung saldo awal Tahun
        $saldoAwalTahunQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($year) {
                $query->whereHas('transaksiMasuk', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '<', $year);
                })->orWhereHas('transaksiKeluar', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '<', $year);
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalTahunQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalTahun = $saldoAwalTahunQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada Tahun tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($year) {
                $query->whereHas('transaksiMasuk', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '=', $year);
                })->orWhereHas('transaksiKeluar', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '=', $year);
                });
            });

        if ($code_kontrakan !== 'all') {
            $transaksiListQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir Tahun
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirTahun = $saldoAwalTahun + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalTahun' => rupiah($saldoAwalTahun),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirTahun' => rupiah($saldoAkhirTahun),
        ]);
    }


    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan Tahun dan tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y');
        } else {
            try {
                // Ubah format tanggal dari 'Y' ke 'Y'
                $date = Carbon::createFromFormat('Y', $date)->format('Y');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y format.'], 400);
            }
        }

        // Pisahkan tahun dan Tahun dari parameter 'date'
        $year = Carbon::createFromFormat('Y', $date)->year;

        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($year) {
                $query->whereYear('tanggal_transaksi', $year);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiMasuk = $transaksiMasukQuery->with('transaksiMasuk')->get();

        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($year) {
                $query->whereYear('tanggal_transaksi', $year);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiKeluar = $transaksiKeluarQuery->with('transaksiKeluar')->get();

        foreach ($transaksiMasuk as $transaksi) {
            $kontrakan = Kontrakan::where('code_kontrakan', $transaksi->code_kontrakan)->first();
            $transaksi->nama_kontrakan = $kontrakan->nama_kontrakan ?? 'Unknown';
        }

        foreach ($transaksiKeluar as $transaksi) {
            $kontrakan = Kontrakan::where('code_kontrakan', $transaksi->code_kontrakan)->first();
            $transaksi->nama_kontrakan = $kontrakan->nama_kontrakan ?? 'Unknown';
        }

        return response()->json([
            'transaksiMasuk' => $transaksiMasuk,
            'transaksiKeluar' => $transaksiKeluar,
        ]);
    }

    public function aktivitas()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.aktivitas', $data);
    }
}
