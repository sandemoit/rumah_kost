<?php

namespace App\Http\Controllers;

use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanCustomController extends Controller
{
    public function custom()
    {
        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Custom',
        ];

        return view('admin.laporan.custom.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal Custom ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            // Ubah format tanggal dari 'd-m-Y' ke 'Y-m-d'
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        // Menghitung saldo awal Custom
        $saldoAwalCustomQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($date) {
                $query->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->whereDate('periode_sewa', '<', $date);
                })->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '<', $date);
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalCustomQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalCustom = $saldoAwalCustomQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada tanggal tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($date, $code_kontrakan) {
                $query->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->whereDate('periode_sewa', '=', $date);
                })
                    ->when($code_kontrakan !== 'all', function ($query) use ($code_kontrakan) {
                        $query->where('code_kontrakan', $code_kontrakan);
                    });
            })
            ->orWhere(function ($query) use ($date, $code_kontrakan) {
                $query->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '=', $date);
                })
                    ->when($code_kontrakan !== 'all', function ($query) use ($code_kontrakan) {
                        $query->where('code_kontrakan', $code_kontrakan);
                    });
            });

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir Custom
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirCustom = $saldoAwalCustom + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalCustom' => rupiah($saldoAwalCustom),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirCustom' => rupiah($saldoAkhirCustom),
        ]);
    }

    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal Custom ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            // Ubah format tanggal dari 'd-m-Y' ke 'Y-m-d'
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        $code_kontrakan = $request->input('book', 'all');

        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->whereDate('periode_sewa', $date);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiMasuk = $transaksiMasukQuery->with('transaksiMasuk')->get();

        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
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
}
