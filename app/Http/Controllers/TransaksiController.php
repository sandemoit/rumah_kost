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
    public function show(Request $request, $code_kontrakan)
    {
        // Ambil data kontrakan berdasarkan code_kontrakan
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();

        // Ambil semua kamar yang terkait dengan kontrakan tersebut
        $kamar = Kamar::where('id_kontrakan', $kontrakan->id)->select('id', 'nama_kamar')->get();
        $countKontrakan = $kamar->count();

        // Ambil data penyewa yang memiliki status putus_kontrak dan terkait dengan kamar yang terkait dengan kontrakan tersebut
        $penyewa = Penyewa::whereHas('kamar', function (Builder $query) use ($kontrakan) {
            $query->select('id', 'nama_kamar')->where('id_kontrakan', $kontrakan->id);
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
            $tanggalPutusKontrak = Carbon::parse($penyewa->tanggal_putus_kontrak)->startOfMonth();

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
        $transaksiList = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where('code_kontrakan', $code_kontrakan)
            ->when($keyword, function (Builder $query, $keyword) {
                return $query->whereHas('kamar', function (Builder $query) use ($keyword) {
                    $query->where('nama_kamar', 'LIKE', "%$keyword%");
                });
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        // Uraikan JSON id_kamar dan dapatkan nama kamar
        foreach ($transaksiList as $transaksi) {
            $idKamarArray = is_string($transaksi->id_kamar) ? json_decode($transaksi->id_kamar, true) : [$transaksi->id_kamar];
            if (is_array($idKamarArray)) {
                $namaKamar = Kamar::whereIn('id', $idKamarArray)->pluck('nama_kamar')->implode(', ');
                $transaksi->nama_kamar = $namaKamar;
                $transaksi->kamar_nama_list = $idKamarArray;
            } else {
                $transaksi->nama_kamar = null;
            }
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
        $transaksiTerakhir = TransaksiMasuk::where('id', $transaksi->id_tipe)
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
        // Cari kontrakan dan harga kamar berdasarkan id_kamar tersebut
        $kamar = Kamar::findOrFail($id);
        $kontrakan = $kamar->kontrakan;
        $nilaiTunggakan = $kamar->harga_kamar;

        // Cari penyewa yang putus kontrak terkait dengan kamar tersebut
        $penyewaPutusKontrak = Penyewa::where('id_kamar', $id)
            ->where('id_kontrakan', $kontrakan->id)
            ->where('status', 'putus_kontrak')
            ->first();

        // Cari transaksi terkait kamar
        $transaksi = TransaksiList::whereJsonContains('id_kamar', $id)->first();

        // Jika tidak ada transaksi atau tidak ada id_tipe, tentukan bulan tunggakan pertama
        if (!$transaksi || !$transaksi->id_tipe) {
            if ($penyewaPutusKontrak) {
                $periodeSewa = Carbon::parse($penyewaPutusKontrak->tanggal_masuk)->format('Y-m-d');

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

        // Cari transaksi masuk terakhir berdasarkan periode_sewa
        $transaksiTerakhir = TransaksiMasuk::where('id', $transaksi->id_tipe)
            ->latest('periode_sewa')
            ->first();

        if ($transaksiTerakhir) {
            // Hitung bulan berikutnya berdasarkan akhir bulan periode sewa terakhir
            $periodeSewa = Carbon::parse($transaksiTerakhir->periode_sewa)->addMonth()->format('Y-m-d');

            return response()->json([
                'periodeTunggakan' => $periodeSewa,
                'periodeTunggakanDeskripsi' => periodeSewa($periodeSewa),
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
            'periodeSewa' => 'required',
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
            $code_masuk = random_int(1000, 9999);

            $transaksiMasuk = TransaksiMasuk::create([
                'code_masuk' => $code_masuk,
                'deskripsi' => $validatedData['deskripsi'],
                'tanggal_transaksi' => $validatedData['tanggalTerima'],
                'periode_sewa' => $validatedData['periodeSewa'],
            ]);

            // Mengambil transaksi terakhir berdasarkan code_kontrakan
            $transaksiTerakhir = TransaksiList::where('code_kontrakan', $validatedData['codeKontrakan'])->latest()->first();

            $saldoTerakhir = $transaksiTerakhir ? $transaksiTerakhir->saldo : 0;

            // Bersihkan nilaiSewa dari karakter non-digit
            $nilaiSewaBersih = preg_replace('/\D/', '', $validatedData['nilaiSewa']);
            $saldo = (int) $saldoTerakhir + (int) $nilaiSewaBersih;

            $code_transaksi = random_int(1000, 9999);

            TransaksiList::create([
                'code_transaksi' => $code_transaksi,
                'code_kontrakan' => $validatedData['codeKontrakan'],
                'id_kamar' => json_encode([$validatedData['kamarPemasukan']]),
                'id_tipe' => $transaksiMasuk->id,
                'tipe' => 'masuk',
                'nominal' => preg_replace('/\D/', '', $validatedData['nilaiSewa']),
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
            'codeKontrakanKeluar' => 'required|string',
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

                // Mengambil transaksi terakhir berdasarkan code_kontrakan
                $transaksiTerakhir = TransaksiList::where('code_kontrakan', $validatedData['codeKontrakanKeluar'])->latest()->first();

                $saldoTerakhir = $transaksiTerakhir ? $transaksiTerakhir->saldo : 0;
                $saldo = (int) $saldoTerakhir - (int) $validatedData['nominalPengeluaran'];
                $code_transaksi = random_int(1000, 9999);

                TransaksiList::create([
                    'code_transaksi' => $code_transaksi,  // Sesuaikan sesuai kebutuhan
                    'code_kontrakan' => $validatedData['codeKontrakanKeluar'],
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
