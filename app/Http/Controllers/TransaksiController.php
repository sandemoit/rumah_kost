<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use App\Models\Penyewa;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiList;
use App\Models\TransaksiMasuk;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show($code_kontrakan)
    {
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();
        $countPintu = Kamar::where('id_kontrakan', $kontrakan->id)->count();
        $kamar = Kamar::where('id_kontrakan', $kontrakan->id)->get();

        // Mengambil transaksi masuk dan keluar
        $transaksiList = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereIn('id_kamar', $kamar->pluck('id'))
            ->whereMonth('created_at', now()->month)
            ->orderBy('created_at', 'desc')
            ->get();

        // Menyiapkan data untuk dikirim ke view
        $data = [
            'pageTitle' => $kontrakan->nama_kontrakan,
            'keterangan' => "Kontrakan $kontrakan->nama_kontrakan $countPintu Pintu",
            'kamar' => $kamar,
            'transaksiList' => $transaksiList
        ];

        // Mengembalikan view dengan data yang sudah diparsing
        return view('admin.transaksi.transaksi', $data);
    }

    public function getKamarData($id)
    {
        // Cari transaksi terkait kamar
        $transaksi = TransaksiList::where('id_kamar', $id)->first();

        // Jika tidak ada transaksi, periksa apakah ada penyewa aktif di kamar tersebut
        if (!$transaksi || !$transaksi->id_tipe) {
            $penyewa = Penyewa::where('id_kamar', $id)->where('status', 'aktif')->first();

            if ($penyewa) {
                // Jika ada penyewa aktif, gunakan tanggal masuk untuk menentukan bulan sewa pertama
                $tanggalMasuk = Carbon::parse($penyewa->tanggal_masuk);
                $bulanDepan = $tanggalMasuk->month;
                $nilaiSewa = Kamar::find($id)->harga_kamar;

                return response()->json([
                    'periodeSewa' => "Bulan $bulanDepan",
                    'nilaiSewa' => $nilaiSewa
                ]);
            } else {
                // Jika tidak ada penyewa aktif, set periodeSewa dan nilaiSewa ke 'N/A'
                return response()->json([
                    'periodeSewa' => 'Bulan N/A',
                    'nilaiSewa' => 'N/A'
                ]);
            }
        }

        // Cari transaksi masuk terakhir berdasarkan tahun dan bulan
        $transaksiTerakhir = TransaksiMasuk::where('id', $transaksi->id_tipe)
            ->select('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->first();

        // Jika ada transaksi terakhir, hitung bulan berikutnya
        $penyewaAktif = Penyewa::where('id', $transaksi->id_tipe)
            ->where('id_kamar', $id)
            ->where('id_kontrakan', $transaksi->id_kontrakan)
            ->where('status', 'aktif')
            ->exists();

        if ($transaksiTerakhir && $penyewaAktif) {
            $bulanTerakhir = $transaksiTerakhir->bulan;
            $bulanDepan = $bulanTerakhir == 12 ? 1 : $bulanTerakhir + 1;
        } else {
            $bulanDepan = $transaksiTerakhir ? $transaksiTerakhir->bulan + 1 : 1;
        }

        // Cari harga kamar
        $kamar = Kamar::find($id);

        return response()->json([
            'periodeSewa' => "Bulan $bulanDepan",
            'nilaiSewa' => $kamar->harga_kamar,
        ]);
    }

    public function store_masuk(Request $request)
    {
        $validatedData = $request->validate([
            'tanggalTerima' => 'required|date',
            'kamarPemasukan' => 'required|integer',
            'periodeSewa' => 'required|integer',
            'tahunSewa' => 'required|integer',
            'nilaiSewa' => 'required|integer',
            'deskripsi' => 'nullable|string',
        ]);

        // Check if a transaction with the same id_kamar, bulan, and tahun already exists (for the current month and year)
        $existingTransaction = TransaksiList::where('id_kamar', $validatedData['kamarPemasukan'])
            ->whereHas('TransaksiMasuk', function (Builder $query) use ($validatedData) {
                $query->where(function (Builder $query) use ($validatedData) {
                    $query->where('bulan', now()->month)
                        ->where('tahun', now()->year);
                })
                    ->orWhere(function (Builder $query) use ($validatedData) {
                        $query->where('bulan', $validatedData['periodeSewa'])
                            ->where('tahun', $validatedData['tahunSewa']);
                    });
            })
            ->first();

        if ($existingTransaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamar ini sudah bayar periode ini.'
            ]);
        }

        DB::transaction(function () use ($validatedData) {
            // Buat transaksi masuk baru
            $code_masuk = random_int(100000, 999999);

            $transaksiMasuk = TransaksiMasuk::create([
                'code_masuk' => $code_masuk,
                'deskripsi' => $validatedData['deskripsi'],
                'tanggal_transaksi' => $validatedData['tanggalTerima'],
                'bulan' => $validatedData['periodeSewa'],
                'tahun' => $validatedData['tahunSewa'],
            ]);

            $transaksiTerakhir = TransaksiList::latest()->first();
            $saldoTerakhir = $transaksiTerakhir ? $transaksiTerakhir->saldo : 0;
            $saldo = (int) $saldoTerakhir + (int) $validatedData['nilaiSewa'];

            $code_transaksi = random_int(100000, 999999);

            TransaksiList::create([
                'code_transaksi' => $code_transaksi,
                'id_kamar' => $validatedData['kamarPemasukan'],
                'id_tipe' => $transaksiMasuk->id,
                'tipe' => 'masuk',
                'nominal' => $validatedData['nilaiSewa'],
                'saldo' => $saldo,
                'created_by' => Auth::user()->id,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function store_keluar(Request $request)
    {
        $validatedData = $request->validate([
            'tanggalPengeluaran' => 'required|date',
            'kamarPengeluaran' => 'required|string',
            'nominalPengeluaran' => 'required|numeric',
            'deskripsiPengeluaran' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $code_keluar = random_int(100000, 999999);

                $transaksiKeluar = TransaksiKeluar::create([
                    'code_keluar' => $code_keluar,  // Sesuaikan sesuai kebutuhan
                    'deskripsi' => $validatedData['deskripsiPengeluaran'],
                    'tanggal_transaksi' => $validatedData['tanggalPengeluaran'],
                ]);

                $transaksiTerakhir = TransaksiList::latest()->first();
                $saldoTerakhir = $transaksiTerakhir ? $transaksiTerakhir->saldo : 0;
                $saldo = (int) $saldoTerakhir - (int) $validatedData['nominalPengeluaran'];
                $code_transaksi = random_int(100000, 999999);

                TransaksiList::create([
                    'code_transaksi' => $code_transaksi,  // Sesuaikan sesuai kebutuhan
                    'id_kamar' => $validatedData['kamarPengeluaran'],
                    'id_tipe' => $transaksiKeluar->id,
                    'tipe' => 'keluar',
                    'nominal' => $validatedData['nominalPengeluaran'],
                    'saldo' => $saldo,
                    'created_by' => Auth::user()->id,
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTransaction($type, $id)
    {
        if ($type === 'masuk') {
            $transaksiMasuk = TransaksiMasuk::findOrFail($id);
            return response()->json($transaksiMasuk);
        } else if ($type === 'keluar') {
            $transaksiKeluar = TransaksiKeluar::findOrFail($id);
            return response()->json($transaksiKeluar);
        }

        return response()->json(['error' => 'Invalid type'], 400);
    }

    public function getSaldoKontrakan($code_kontrakan)
    {
        // Debugging: Log atau echo code_kontrakan
        Log::info('Code Kontrakan: ' . $code_kontrakan);

        // Ambil kontrakan berdasarkan code_kontrakan
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();

        // Debugging: Log kontrakan
        Log::info('Kontrakan: ' . $kontrakan);

        // Ambil semua kamar yang terkait dengan kontrakan tersebut
        $kamarIds = Kamar::where('id_kontrakan', $kontrakan->id)->pluck('id');

        // Ambil semua transaksi berdasarkan id_kamar
        $transaksiList = TransaksiList::whereIn('id_kamar', $kamarIds)->get();

        // Ambil saldo terakhir dari transaksi tersebut
        $saldo = $transaksiList->sortByDesc('created_at')->first()->saldo;

        return response()->json([
            'saldo' => $saldo
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransaksiList $transaksi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteMasuk($id)
    {
        $transaksiMasuk = TransaksiMasuk::findOrFail($id);
        $transaksiMasuk->delete();

        TransaksiList::where('id_tipe', $transaksiMasuk->id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Transaksi masuk berhasil dihapus.']);
    }

    public function deleteKeluar($id)
    {
        $transaksiKeluar = TransaksiKeluar::findOrFail($id);
        $transaksiKeluar->delete();

        TransaksiList::where('id_tipe', $transaksiKeluar->id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Transaksi keluar berhasil dihapus.']);
    }
}
