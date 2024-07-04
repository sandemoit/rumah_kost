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

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show(Request $request, $code_kontrakan)
    {
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();
        $kamar = Kamar::where('id_kontrakan', $kontrakan->id)->get();
        $countPintu = $kamar->count();

        // Mengambil bulan dan tahun dari URL
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Mengambil transaksi masuk dan keluar
        $keyword = $request->input('search');
        $transaksiList = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->when($keyword, function (Builder $query, $keyword) {
                return $query->whereHas('kamar', function (Builder $query) use ($keyword) {
                    $query->where('nama_kamar', 'LIKE', "%$keyword%");
                });
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Uraikan JSON id_kamar dan dapatkan nama kamar
        foreach ($transaksiList as $transaksi) {
            $idKamarArray = json_decode($transaksi->id_kamar);
            if (is_array($idKamarArray)) {
                $transaksi->nama_kamar = Kamar::whereIn('id', $idKamarArray)->pluck('nama_kamar')->implode(', ');
            } else {
                $transaksi->nama_kamar = 'Tidak diketahui';
            }
        }

        // Menyiapkan data untuk dikirim ke view
        $data = [
            'pageTitle' => $kontrakan->nama_kontrakan,
            'keterangan' => "Kontrakan $kontrakan->nama_kontrakan $countPintu Pintu",
            'kamar' => $kamar,
            'transaksiList' => $transaksiList,
            'code_kontrakan' => $code_kontrakan
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

    public function getSaldoKontrakan($code_kontrakan)
    {
        // Ambil kontrakan berdasarkan code_kontrakan
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();

        // Ambil semua kamar yang terkait dengan kontrakan tersebut
        $kamarIds = Kamar::where('id_kontrakan', $kontrakan->id)->pluck('id');

        // Ambil semua transaksi
        $transaksiList = TransaksiList::all();

        $transaksiFiltered = $transaksiList->filter(function ($transaksi) use ($kamarIds) {
            $transaksiKamarIds = json_decode($transaksi->id_kamar);
            return !array_diff($transaksiKamarIds, $kamarIds->toArray());
        });

        // Ambil saldo terakhir dari transaksi tersebut
        $saldo = $transaksiFiltered->isEmpty() ? 0 : $transaksiFiltered->sortByDesc('created_at')->first()->saldo;

        return response()->json([
            'saldo' => $saldo
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
            $code_masuk = random_int(1000, 9999);

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

            $code_transaksi = random_int(1000, 9999);

            TransaksiList::create([
                'code_transaksi' => $code_transaksi,
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'id_tipe' => $transaksiMasuk->id,
                'tipe' => 'masuk',
                'nominal' => $validatedData['nilaiSewa'],
                'saldo' => $saldo,
                'created_by' => Auth::user()->id,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan.'
        ]);
    }

    public function store_keluar(Request $request)
    {
        $validatedData = $request->validate([
            'tanggalPengeluaran' => 'required|date',
            'kamarPengeluaran' => 'required|array',
            'kamarPengeluaran.*' => 'integer|exists:kamar,id',
            'nominalPengeluaran' => 'required|numeric',
            'deskripsiPengeluaran' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                // Mengambil semua ID kamar jika 'All' dipilih
                if ($validatedData['kamarPengeluaran'] === 'all') {
                    $id_kamar = Kamar::pluck('id')->toArray();
                } else {
                    $id_kamar = $validatedData['kamarPengeluaran'];
                }

                // Mengubah array ID kamar menjadi JSON string
                $id_kamar_json = json_encode($id_kamar);

                $code_keluar = random_int(1000, 9999);

                $transaksiKeluar = TransaksiKeluar::create([
                    'code_keluar' => $code_keluar,  // Sesuaikan sesuai kebutuhan
                    'deskripsi' => $validatedData['deskripsiPengeluaran'],
                    'tanggal_transaksi' => $validatedData['tanggalPengeluaran'],
                ]);

                $transaksiTerakhir = TransaksiList::latest()->first();
                $saldoTerakhir = $transaksiTerakhir ? $transaksiTerakhir->saldo : 0;
                $saldo = (int) $saldoTerakhir - (int) $validatedData['nominalPengeluaran'];
                $code_transaksi = random_int(1000, 9999);

                TransaksiList::create([
                    'code_transaksi' => $code_transaksi,  // Sesuaikan sesuai kebutuhan
                    'id_kamar' => $id_kamar_json,
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

    /**
     * Update the specified resource in storage.
     */
    public function update_masuk(Request $request, $idTrx)
    {
        $validatedData = $request->validate([
            'tanggalTerima' => 'required|date',
            'kamarPemasukan' => 'required|integer',
            'periodeSewa' => 'required|integer',
            'tahunSewa' => 'required|integer',
            'nilaiSewa' => 'required|integer',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $transaksi = TransaksiList::findOrFail($idTrx);

            $transaksiMasuk = TransaksiMasuk::findOrFail($transaksi->id_tipe);
            $transaksiMasuk->update([
                'tanggal_transaksi' => $validatedData['tanggalTerima'],
                'bulan' => $validatedData['periodeSewa'],
                'tahun' => $validatedData['tahunSewa'],
                'deskripsi' => $validatedData['deskripsi'],
                'updated_at' => now(),
            ]);

            // Hitung saldo baru
            $saldoSebelum = $transaksi->saldo;
            $saldoBaru = $saldoSebelum - $transaksi->nominal + $validatedData['nilaiSewa'];

            $transaksi->update([
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'nominal' => $validatedData['nilaiSewa'],
                'saldo' => $saldoBaru,
                'created_by' => Auth::user()->id,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Transaksi berhasil diupdate.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_keluar(Request $request, $idTrx)
    {
        $validatedData = $request->validate([
            'tanggalPengeluaran' => 'required|date',
            'kamarPengeluaran' => 'required|array',
            'kamarPengeluaran.*' => 'integer|exists:kamar,id',
            'nominalPengeluaran' => 'required|integer',
            'deskripsiPengeluaran' => 'nullable|string',
        ]);

        try {
            $transaksi = TransaksiList::findOrFail($idTrx);

            $transaksiKeluar = TransaksiKeluar::findOrFail($transaksi->id_tipe);
            $transaksiKeluar->update([
                'tanggal_transaksi' => $validatedData['tanggalPengeluaran'],
                'deskripsi' => $validatedData['deskripsiPengeluaran'],
                'updated_at' => now(),
            ]);

            // Hitung saldo baru
            $saldoSebelum = $transaksi->saldo;
            $saldoBaru = $saldoSebelum + $transaksi->nominal - $validatedData['nominalPengeluaran'];

            // Mengambil semua ID kamar jika 'All' dipilih
            if ($validatedData['kamarPengeluaran'] === 'all') {
                $id_kamar = Kamar::pluck('id')->toArray();
            } else {
                $id_kamar = $validatedData['kamarPengeluaran'];
            }

            // Mengubah array ID kamar menjadi JSON string
            $id_kamar_json = json_encode($id_kamar);

            $transaksi->update([
                'id_kamar' => $id_kamar_json,
                'nominal' => $validatedData['nominalPengeluaran'],
                'saldo' => $saldoBaru,
                'created_by' => Auth::user()->id,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Transaksi berhasil diupdate.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteMasuk($id)
    {
        try {
            $transaksi = TransaksiList::where('id', $id)->firstOrFail();

            TransaksiList::where('id', $id)->delete();
            TransaksiMasuk::findOrFail($transaksi->id_tipe)->delete();

            return response()->json(['status' => 'success', 'message' => 'Transaksi masuk berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus data'], 500);
        }
    }

    public function deleteKeluar($id)
    {
        try {
            $transaksi = TransaksiList::where('id', $id)->firstOrFail();

            TransaksiList::where('id', $id)->delete();
            TransaksiKeluar::findOrFail($transaksi->id_tipe)->delete();

            return response()->json(['status' => 'success', 'message' => 'Transaksi keluar berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus data'], 500);
        }
    }
}
