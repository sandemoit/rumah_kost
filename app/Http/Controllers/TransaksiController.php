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
        // Ambil data kontrakan berdasarkan code_kontrakan
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();

        // Ambil semua kamar yang terkait dengan kontrakan tersebut
        $kamar = Kamar::where('id_kontrakan', $kontrakan->id)->select('id', 'nama_kamar')->get();
        $countKontrakan = $kamar->count();

        // Ambil data penyewa yang memiliki status putus_kontrak dan terkait dengan kamar yang terkait dengan kontrakan tersebut
        $penyewa = Penyewa::whereHas('kamar', function (Builder $query) use ($kontrakan) {
            $query->where('id_kontrakan', $kontrakan->id);
        })->where('status', 'putus_kontrak')->with('kamar:id,nama_kamar')->get();

        // Menyaring kamar dengan penyewa yang menunggak
        $kamarTunggakan = $penyewa->filter(function ($penyewa) {
            // Ambil semua transaksi masuk yang terkait dengan kamar penyewa ini
            $transaksiList = TransaksiList::whereJsonContains('id_kamar', $penyewa->id_kamar)
                ->where('tipe', 'masuk')
                ->get();

            // Jika tidak ada transaksi, tampilkan kamar tersebut
            if ($transaksiList->isEmpty()) {
                return true;
            }

            // Ambil periode saat ini dan tanggal putus kontrak
            $periodeSaatIni = Carbon::now()->startOfMonth();
            $tanggalPutusKontrak = Carbon::parse($penyewa->tanggal_keluar)->startOfMonth();

            // Periksa setiap transaksi untuk menentukan apakah periode sewa telah mencapai tanggal putus kontrak
            $periodeSewaTerakhir = null;
            foreach ($transaksiList as $transaksi) {
                $transaksiMasuk = TransaksiMasuk::where('id', $transaksi->id_tipe)->latest('periode_sewa')->first();
                if ($transaksiMasuk) {
                    $periodeSewa = Carbon::parse($transaksiMasuk->periode_sewa)->startOfMonth();
                    $periodeSewaTerakhir = $periodeSewa;

                    // Jika periode sewa terakhir sama dengan atau setelah tanggal putus kontrak, jangan tampilkan
                    if ($periodeSewa->eq($tanggalPutusKontrak) || $periodeSewa->gt($tanggalPutusKontrak)) {
                        return false;
                    }
                }
            }

            // Jika periode sewa terakhir belum terpenuhi hingga periode saat ini, tampilkan
            if ($periodeSewaTerakhir && $periodeSewaTerakhir->lt($periodeSaatIni)) {
                return true;
            }

            // Jika tidak ada transaksi yang memenuhi syarat di atas, tampilkan kamar tersebut
            return true;
        })->map(function ($penyewa) {
            return $penyewa->kamar;
        })->filter()->flatten()->unique('id');

        // Mengambil bulan dan tahun dari URL
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Mengambil transaksi masuk dan keluar berdasarkan code_kontrakan
        $keyword = $request->input('search');

        // Mengambil transaksi berdasarkan bulan dan tahun
        $transaksiList = TransaksiList::withTransactions($code_kontrakan, $month, $year, $keyword)
            // ->orderBy('id', 'desc')
            ->paginate(10);

        // Inisialisasi saldo awal
        $saldo = 0;

        // Iterasi transaksi
        foreach ($transaksiList as $transaksi) {
            // Uraikan JSON id_kamar dan dapatkan nama kamar
            $idKamarArray = is_string($transaksi->id_kamar) ? json_decode($transaksi->id_kamar, true) : [$transaksi->id_kamar];
            if (is_array($idKamarArray)) {
                $namaKamar = Kamar::whereIn('id', $idKamarArray)->pluck('nama_kamar')->implode(', ');
                $transaksi->nama_kamar = $namaKamar;
                $transaksi->kamar_nama_list = $idKamarArray;
            } else {
                $transaksi->nama_kamar = 'Undefined';
            }

            // Hitung saldo berdasarkan tipe transaksi
            if ($transaksi->tipe === 'masuk') {
                $saldo += $transaksi->nominal;
            } elseif ($transaksi->tipe === 'keluar') {
                $saldo -= $transaksi->nominal;
            }

            // Set saldo pada transaksi saat ini
            $transaksi->saldo = $saldo;
        }

        // Menyiapkan data untuk dikirim ke view
        $data = [
            'pageTitle' => $kontrakan->nama_kontrakan,
            'keterangan' => "Kontrakan $kontrakan->nama_kontrakan $countKontrakan Pintu",
            'kamar' => $kamar,
            'kamarTunggakan' => $kamarTunggakan,
            'transaksiList' => $transaksiList,
            'code_kontrakan' => $code_kontrakan
        ];

        // Mengembalikan view dengan data yang sudah diparsing
        return view('admin.transaksi.transaksi', $data);
    }

    public function getKamarData($id)
    {
        // Cari transaksi terkait kamar
        $transaksi = TransaksiList::whereJsonContains('id_kamar', $id)->first();

        // Cari kontrakan berdasarkan id_kamar tersebut
        $kontrakan = Kamar::find($id)->kontrakan;

        // Cari harga kamar
        $hargaKamar = Kamar::find($id)->harga_kamar;

        // Jika tidak ada transaksi, periksa apakah ada penyewa aktif di kamar tersebut
        if (!$transaksi || !$transaksi->id_tipe) {
            $penyewa = Penyewa::where('id_kamar', $id)->where('status', 'aktif')->first();

            if ($penyewa) {
                // Jika ada penyewa aktif, gunakan tanggal masuk untuk menentukan bulan sewa pertama
                $periodeSewa = Carbon::parse($penyewa->tanggal_masuk)->format('Y-m-d');

                return response()->json([
                    'periodeDeskripsi' => periodeSewa($periodeSewa),
                    'periodeSewa' => $periodeSewa,
                    'nilaiSewa' => nominal($hargaKamar),
                    'codeKontrakan' => $kontrakan->code_kontrakan,
                ]);
            } else {
                // Jika tidak ada penyewa aktif, set periodeSewa dan nilaiSewa ke 'N/A'
                return response()->json([
                    'periodeDeskripsi' => 'N/A',
                    'nilaiSewa' => 'N/A'
                ]);
            }
        }

        // Cari transaksi masuk terakhir berdasarkan periode_sewa
        $transaksiTerakhir = TransaksiMasuk::where('id_kamar', 'LIKE', '%"' . $id . '"%')
            ->select('periode_sewa')
            ->latest('periode_sewa')
            ->first();

        // Cek apakah ada penyewa aktif di kamar tersebut
        $penyewaAktif = Penyewa::where('id_kamar', $id)
            ->where('id_kontrakan', $kontrakan->id)
            ->where('status', 'aktif')
            ->exists();

        // Jika ada transaksi terakhir dan penyewa aktif di kamar tersebut
        if ($transaksiTerakhir && $penyewaAktif) {
            // Hitung bulan berikutnya menggunakan Carbon
            $periodeSewa = Carbon::parse($transaksiTerakhir->periode_sewa)->addMonth()->format('Y-m-d');

            return response()->json([
                'periodeDeskripsi' => periodeSewa($periodeSewa),
                'periodeSewa' => $periodeSewa,
                'nilaiSewa' => nominal($hargaKamar),
                'codeKontrakan' => $kontrakan->code_kontrakan,
            ]);
        } else {
            // Jika tidak ada transaksi terakhir atau tidak ada penyewa aktif, set periodeSewa dan nilaiSewa ke 'N/A'
            return response()->json([
                'periodeDeskripsi' => 'N/A',
                'nilaiSewa' => 'N/A'
            ]);
        }
    }

    public function getTunggakan($id)
    {
        // Cari kamar dan harga kamar terkait
        $kamar = Kamar::findOrFail($id);
        $kontrakan = $kamar->kontrakan;
        $nilaiTunggakan = $kamar->harga_kamar;

        // Cari penyewa yang putus kontrak terkait dengan kamar tersebut
        $penyewaKeluar = Penyewa::where('id_kamar', $id)
            ->where('id_kontrakan', $kontrakan->id)
            ->where('status', 'putus_kontrak')
            ->firstOrFail();

        // Cari transaksi terkait kamar dan penyewa di TransaksiList
        $transaksi = TransaksiList::where('id_kamar', 'LIKE', '%"' . $id . '"%')
            ->where('id_penyewa', $penyewaKeluar->id)
            ->where('code_kontrakan', $kontrakan->code_kontrakan)
            ->where('tipe', 'masuk')
            ->latest('id_tipe')
            ->first();

        // Jika tidak ada transaksi atau tidak ada id_tipe, tentukan bulan tunggakan pertama
        if (empty($transaksi) || empty($transaksi->id_tipe)) {
            if (!empty($penyewaKeluar)) {
                $periodeSewa = Carbon::parse($penyewaKeluar->tanggal_masuk)->format('Y-m-d');

                return response()->json([
                    'periodeTunggakanDeskripsi' => periodeSewa($periodeSewa),
                    'periodeTunggakan' => $periodeSewa,
                    'nilaiTunggakan' => nominal($nilaiTunggakan),
                    'codeKontrakan' => $kontrakan->code_kontrakan,
                ]);
            } else {
                return response()->json([
                    'periodeTunggakanDeskripsi' => 'N/A',
                    'nilaiTunggakan' => 'N/A'
                ]);
            }
        }

        if ($transaksi) {
            // Hitung bulan berikutnya jika periode_sewa transaksi terakhir belum melewati tanggal_keluar
            $tanggalKeluar = Carbon::parse($penyewaKeluar->tanggal_keluar);
            $periodeSewa = Carbon::parse($transaksi->transaksiMasuk->periode_sewa);

            if ($periodeSewa >= $tanggalKeluar) {
                return response()->json([
                    'periodeTunggakanDeskripsi' => 'N/A',
                    'nilaiTunggakan' => 'N/A'
                ]);
            }

            // Jika periode sewa transaksi terakhir belum melewati tanggal keluar
            $periodeTunggakan = $periodeSewa->addMonth()->format('Y-m-d');

            return response()->json([
                'periodeTunggakan' => $periodeTunggakan,
                'periodeTunggakanDeskripsi' => periodeSewa($periodeTunggakan),
                'nilaiTunggakan' => nominal($nilaiTunggakan),
                'codeKontrakan' => $kontrakan->code_kontrakan,
            ]);
        } else {
            return response()->json([
                'periodeTunggakanDeskripsi' => 'N/A',
                'nilaiTunggakan' => 'N/A'
            ]);
        }
    }

    public function getSaldoKontrakan($code_kontrakan)
    {
        // Ambil kontrakan berdasarkan code_kontrakan
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();

        // Ambil semua kamar yang terkait dengan kontrakan tersebut
        $kamarIds = Kamar::where('id_kontrakan', $kontrakan->id)->pluck('id');

        // Ambil semua transaksi yang terkait dengan kamar tersebut
        $transaksiList = TransaksiList::all();

        // Filter transaksi yang terkait dengan kamar dalam kontrakan
        $transaksiFiltered = $transaksiList->filter(function ($transaksi) use ($kamarIds) {
            $transaksiKamarIds = json_decode($transaksi->id_kamar);
            return !array_diff($transaksiKamarIds, $kamarIds->toArray());
        });

        // Kalkulasi saldo berdasarkan nominal yang masuk dan keluar
        $saldo = $transaksiFiltered->reduce(function ($carry, $transaksi) {
            if ($transaksi->tipe == 'masuk') {
                return $carry + $transaksi->nominal;
            } elseif ($transaksi->tipe == 'keluar') {
                return $carry - $transaksi->nominal;
            }
            return $carry;
        }, 0);

        return response()->json([
            'saldo' => rupiah($saldo)
        ]);
    }

    public function getAllSaldo()
    {
        // Menghitung total pemasukan
        $totalPemasukan = TransaksiList::where('tipe', 'masuk')
            ->sum('nominal');

        // Menghitung total pengeluaran
        $totalPengeluaran = TransaksiList::where('tipe', 'keluar')
            ->sum('nominal');

        // Menghitung total saldo (pemasukan - pengeluaran)
        $totalSaldo = $totalPemasukan - $totalPengeluaran;

        // Mengembalikan response JSON dengan total saldo
        return response()->json([
            'totalSaldo' => $totalSaldo,
        ]);
    }

    public function store_masuk(Request $request)
    {
        $validatedData = $request->validate([
            'tanggalTerima' => 'required|date',
            'kamarPemasukan' => 'required|integer',
            'periodeSewa' => 'required|date',
            'deskripsi' => 'required',
            'nilaiSewa' => 'required|string',
            'codeKontrakan' => 'required|string',
        ]);

        // Cek apakah sudah ada transaksi untuk periode ini di kamar ini
        $existingTransaction = TransaksiList::whereJsonContains('id_kamar', $validatedData['kamarPemasukan'])
            ->whereHas('transaksiMasuk', function (Builder $query) use ($validatedData) {
                $query->where('periode_sewa', $validatedData['periodeSewa']);
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

            $kamar = Penyewa::findOrFail($validatedData['kamarPemasukan']);
            $penyewa = Kamar::findOrFail($kamar->id_kamar);

            $transaksiMasuk = TransaksiMasuk::create([
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'deskripsi' => $validatedData['deskripsi'],
                'tanggal_transaksi' => $validatedData['tanggalTerima'],
                'periode_sewa' => $validatedData['periodeSewa'],
            ]);

            $code_transaksi = random_int(1000, 9999);

            TransaksiList::create([
                'code_transaksi' => $code_transaksi,
                'code_kontrakan' => $validatedData['codeKontrakan'],
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'id_penyewa' => $penyewa->id,
                'id_tipe' => $transaksiMasuk->id,
                'tipe' => 'masuk',
                'nominal' => preg_replace('/\D/', '', $validatedData['nilaiSewa']),
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
            'nominalPengeluaran' => 'required|numeric',
            'deskripsiPengeluaran' => 'required|string',
            'codeKontrakanKeluar' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                // Cek apakah "all" dipilih, jika ya, ambil semua ID kamar berdasarkan code_kontrakan
                if (in_array('all', $validatedData['kamarPengeluaran'])) {
                    // Mengambil semua ID kamar yang terkait dengan code_kontrakan
                    $id_kamar = Kamar::whereHas('kontrakan', function ($query) use ($validatedData) {
                        $query->where('code_kontrakan', $validatedData['codeKontrakanKeluar']);
                    })->pluck('id')->toArray();
                } else {
                    $id_kamar = $validatedData['kamarPengeluaran'];
                }

                // Mengubah array ID kamar menjadi JSON string
                $id_kamar_json = json_encode(array_map('strval', $id_kamar));

                $code_keluar = random_int(1000, 9999);

                $transaksiKeluar = TransaksiKeluar::create([
                    'code_keluar' => $code_keluar,
                    'deskripsi' => $validatedData['deskripsiPengeluaran'],
                    'tanggal_transaksi' => $validatedData['tanggalPengeluaran'],
                ]);

                $code_transaksi = random_int(1000, 9999);

                TransaksiList::create([
                    'code_transaksi' => $code_transaksi,
                    'code_kontrakan' => $validatedData['codeKontrakanKeluar'],
                    'id_kamar' => $id_kamar_json,
                    'id_tipe' => $transaksiKeluar->id,
                    'tipe' => 'keluar',
                    'nominal' => $validatedData['nominalPengeluaran'],
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
            'periodeSewa' => 'required|date',
            'nilaiSewa' => 'required|numeric',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $transaksi = TransaksiList::findOrFail($idTrx);
            $nilaiSewa = preg_replace('/\D/', '', $validatedData['nilaiSewa']);

            $kamar = Penyewa::findOrFail($validatedData['kamarPemasukan']);
            $penyewa = Kamar::findOrFail($kamar->id_kamar);

            $transaksiMasuk = TransaksiMasuk::findOrFail($transaksi->id_tipe);
            $transaksiMasuk->update([
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'deskripsi' => $validatedData['deskripsi'],
                'tanggal_transaksi' => $validatedData['tanggalTerima'],
                'periode_sewa' => $validatedData['periodeSewa'],
                'updated_at' => now(),
            ]);

            $transaksi->update([
                'id_penyewa' => $penyewa->id,
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'nominal' => $nilaiSewa,
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
            'nominalPengeluaran' => 'required|numeric',
            'deskripsiPengeluaran' => 'nullable|string',
        ]);

        try {
            $transaksi = TransaksiList::findOrFail($idTrx);
            $nominalKeluar = preg_replace('/\D/', '', $validatedData['nominalPengeluaran']);

            $transaksiKeluar = TransaksiKeluar::findOrFail($transaksi->id_tipe);
            $transaksiKeluar->update([
                'tanggal_transaksi' => $validatedData['tanggalPengeluaran'],
                'deskripsi' => $validatedData['deskripsiPengeluaran'],
                'updated_at' => now(),
            ]);

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
                'nominal' => $nominalKeluar,
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
