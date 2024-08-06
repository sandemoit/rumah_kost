<?php

namespace App\Http\Controllers;

use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanBulananController extends Controller
{
    public function bulanan()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.umum', $data);
    }

    public function umum()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan bulan dan tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m');
        } else {
            try {
                // Ubah format tanggal dari 'Y-m' ke 'Y-m'
                $date = Carbon::createFromFormat('Y-m', $date)->format('Y-m');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y-m format.'], 400);
            }
        }

        // Pisahkan tahun dan bulan dari parameter 'date'
        $year = Carbon::createFromFormat('Y-m', $date)->year;
        $month = Carbon::createFromFormat('Y-m', $date)->month;

        // Menghitung saldo awal Bulan
        $saldoAwalBulanQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($year, $month) {
                $query->whereHas('transaksiMasuk', function ($query) use ($year, $month) {
                    $query->whereYear('tanggal_transaksi', '<', $year)
                        ->orWhere(function ($query) use ($year, $month) {
                            $query->whereYear('tanggal_transaksi', '=', $year)
                                ->whereMonth('tanggal_transaksi', '<', $month);
                        });
                })->orWhereHas('transaksiKeluar', function ($query) use ($year, $month) {
                    $query->whereYear('tanggal_transaksi', '<', $year)
                        ->orWhere(function ($query) use ($year, $month) {
                            $query->whereYear('tanggal_transaksi', '=', $year)
                                ->whereMonth('tanggal_transaksi', '<', $month);
                        });
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalBulanQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalBulan = $saldoAwalBulanQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada bulan tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($month, $year) {
                $query->whereHas('transaksiMasuk', function ($query) use ($month, $year) {
                    $query->whereYear('tanggal_transaksi', '=', $year)
                        ->whereMonth('tanggal_transaksi', '=', $month);
                })->orWhereHas('transaksiKeluar', function ($query) use ($month, $year) {
                    $query->whereYear('tanggal_transaksi', '=', $year)
                        ->whereMonth('tanggal_transaksi', '=', $month);
                });
            });

        if ($code_kontrakan !== 'all') {
            $transaksiListQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir Bulan
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirBulan = $saldoAwalBulan + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalBulan' => rupiah($saldoAwalBulan),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirBulan' => rupiah($saldoAkhirBulan),
        ]);
    }

    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan bulan dan tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m');
        } else {
            try {
                // Ubah format tanggal dari 'Y-m' ke 'Y-m'
                $date = Carbon::createFromFormat('Y-m', $date)->format('Y-m');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y-m format.'], 400);
            }
        }

        // Pisahkan tahun dan bulan dari parameter 'date'
        $year = Carbon::createFromFormat('Y-m', $date)->year;
        $month = Carbon::createFromFormat('Y-m', $date)->month;

        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($year, $month) {
                $query->whereYear('tanggal_transaksi', $year)
                    ->whereMonth('tanggal_transaksi', $month);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiMasuk = $transaksiMasukQuery->with('transaksiMasuk')->get();

        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($year, $month) {
                $query->whereYear('tanggal_transaksi', $year)
                    ->whereMonth('tanggal_transaksi', $month);
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
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.aktivitas', $data);
    }
}
