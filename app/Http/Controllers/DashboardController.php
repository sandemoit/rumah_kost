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
                $lastTransaction = TransaksiList::where('id_penyewa', $penyewa->id)
                    ->whereHas('transaksiMasuk', function ($query) {
                        $query->orderBy('tanggal_transaksi', 'desc');
                    })
                    ->with(['transaksiMasuk' => function ($query) {
                        $query->orderBy('tanggal_transaksi', 'desc');
                    }])
                    ->first();

                if ($lastTransaction) {
                    $dueDate = Carbon::parse($penyewa->tanggal_masuk)->addMonth();
                    $currentDate = Carbon::now();

                    // Jika tanggal transaksi terakhir sudah lebih dari satu bulan
                    return $dueDate->lt($currentDate);
                }
                return false;
            })->count();

            return [
                'nama_kontrakan' => $item->nama_kontrakan,
                'totalKamar' => $countKamar,
                'terisi' => $terisi,
                'kosong' => $kosong,
                'nunggak' => $nunggak
            ];
        });

        return view('admin.dashboard', [
            'kontrakan' => $dataKontrakan,
            'pageTitle' => 'Dashboard',
        ]);
    }
}
