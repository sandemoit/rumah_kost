<?php

namespace App\Http\Controllers;

use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $kontrakan = Kontrakan::with(['kamar', 'penyewa'])->get();

        // Loop kontrakan untuk mendapatkan kamar dan penyewa
        $dataKontrakan = $kontrakan->map(function ($item) {
            $countKamar = $item->kamar->count();
            $terisi = $item->penyewa->where('status', 'aktif')->count();
            $kosong = $countKamar - $terisi;

            // Cek penyewa yang menunggak
            $nunggak = $item->penyewa->filter(function ($penyewa) {
                // Ambil transaksi masuk terakhir dari TransaksiMasuk melalui TransaksiList
                $lastTransaction = TransaksiList::where('id_penyewa', $penyewa->id)
                    ->with(['transaksiMasuk' => function ($query) {
                        $query->orderBy('tanggal_transaksi', 'desc');
                    }])
                    ->first();

                if ($lastTransaction && $lastTransaction->transaksiMasuk) {
                    $tanggalMasuk = Carbon::parse($penyewa->tanggal_masuk);
                    $tanggalTransaksiTerakhir = Carbon::parse($lastTransaction->transaksiMasuk->tanggal_transaksi);
                    $currentDate = Carbon::now();

                    // Tanggal jatuh tempo (1 bulan setelah tanggal transaksi terakhir)
                    $dueDate = $tanggalTransaksiTerakhir->addMonth();

                    // Jika tanggal saat ini melewati dueDate dan status penyewa aktif, maka dianggap menunggak
                    return $currentDate->gt($dueDate) && $penyewa->status === 'aktif';
                }

                return false; // Jika tidak ada transaksi, penyewa tidak dianggap menunggak
            })->count();

            return [
                'nama_kontrakan' => $item->nama_kontrakan,
                'totalKamar' => $countKamar,
                'terisi' => $terisi,
                'kosong' => $kosong,
                'nunggak' => $nunggak // Jumlah kamar yang menunggak
            ];
        });

        return view('admin.dashboard', [
            'kontrakan' => $dataKontrakan,
            'pageTitle' => 'Dashboard',
        ]);
    }
}
