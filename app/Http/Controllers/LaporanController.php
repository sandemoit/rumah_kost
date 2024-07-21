<?php

namespace App\Http\Controllers;

use App\Models\Kontrakan;
use App\Models\Laporan;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiList;
use App\Models\TransaksiMasuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function harian()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        $date = $request->input('lap_tgl');
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        // Mengambil transaksi berdasarkan tanggal_transaksi dari transaksiMasuk dan transaksiKeluar
        $transaksiList = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            })->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            })->get();

        // Menghitung saldo awal hari untuk setiap code_kontrakan
        $saldoAwalHari = TransaksiList::select('code_kontrakan', DB::raw('SUM(nominal) as saldo_awal'))
            ->where(function ($query) use ($date) {
                $query->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '<', $date);
                });
            })
            ->groupBy('code_kontrakan')
            ->get()
            ->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada tanggal tertentu
        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir hari
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirHari = $saldoAwalHari + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalHari' => rupiah($saldoAwalHari),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirHari' => rupiah($saldoAkhirHari),
        ]);
    }

    public function getAllExIn(Request $request)
    {
        $date = $request->lap_tgl;

        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            $date = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        }

        // Mengambil data transaksi masuk berdasarkan tanggal transaksi
        $transaksiMasuk = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            })
            ->with('transaksiMasuk')
            ->get();

        // Mengambil data transaksi keluar berdasarkan tanggal transaksi
        $transaksiKeluar = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            })
            ->with('transaksiKeluar')
            ->get();

        // Menambahkan nama_kontrakan berdasarkan code_kontrakan
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
            'date' => $date
        ]);
    }

    public function umum()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.umum', $data);
    }

    public function aktivitas()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.umum', $data);
    }

    public function ringkasan()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.umum', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Laporan $laporan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laporan $laporan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Laporan $laporan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laporan $laporan)
    {
        //
    }
}
