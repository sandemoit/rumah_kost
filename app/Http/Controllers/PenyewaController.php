<?php

namespace App\Http\Controllers;

use App\Models\Penyewa;
use App\Models\Kamar;
use App\Models\Kontrakan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PenyewaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $kontrakans = Kontrakan::select('id', 'nama_kontrakan')->get();
        $kamar = Kamar::select('id', 'nama_kamar', 'id_kontrakan')->get();
        $penyewa = Penyewa::with(['kamar:id,nama_kamar,id_kontrakan'])
            ->select('id', 'nama_penyewa', 'status', 'nomor_wa', 'id_kamar', 'id_kontrakan', 'tanggal_masuk')
            ->get();

        // Check which rooms are full
        $kamarTerisi = Penyewa::where('status', 'aktif')->pluck('id_kamar')->toArray();

        $keyword = $request->input('search');
        $penyewa = Penyewa::when($keyword, function ($query, $keyword) {
            return $query->where(function ($query) use ($keyword) {
                $query->where('nama_penyewa', 'LIKE', "%$keyword%")
                    ->orWhere('nomor_wa', 'LIKE', "%$keyword%");
            });
        })->paginate(10);

        $data = [
            'pageTitle' => 'Penyewa',
            'kontrakan' => $kontrakans,
            'penyewa' => $penyewa,
            'kamar' => $kamar,
            'kamarTerisi' => $kamarTerisi,
        ];

        return view('admin.transaksi.penyewa', $data);
    }

    public function getKamarByKontrakan($kontrakanId)
    {
        $kamar = Kamar::select('id', 'nama_kamar')
            ->where('id_kontrakan', $kontrakanId)
            ->withCount(['penyewa' => function ($query) {
                $query->where('status', 'aktif');
            }])
            ->get()
            ->map(function ($item) {
                $item->is_full = $item->penyewa_count > 0;
                return $item;
            });

        return response()->json($kamar);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_penyewa' => 'required|string|max:255',
            'nomor_wa' => 'required|string|max:15',
            'tanggal_masuk' => 'required|date',
            'id_kontrakan' => 'required|exists:kontrakan,id',
            'id_kamar' => 'required|exists:kamar,id',
        ]);

        $tanggal_masuk = Carbon::parse($request->tanggal_masuk);
        $status = $tanggal_masuk->isToday() ? 'aktif' : 'tidak_aktif';

        // Cek apakah kamar sudah diisi oleh penyewa lain yang aktif
        $penyewaAktif = Penyewa::where('id_kontrakan', $request->id_kontrakan)
            ->where('id_kamar', $request->id_kamar)
            ->where('status', 'aktif')
            ->exists();

        if ($penyewaAktif) {
            return redirect()->back()->with(['failed' => 'Kamar sudah diisi oleh penyewa lain yang aktif.'])->withInput();
        }

        try {
            Penyewa::create([
                'nama_penyewa' => $request->nama_penyewa,
                'nomor_wa' => $request->nomor_wa,
                'status' => $status,
                'tanggal_masuk' => $tanggal_masuk,
                'id_kontrakan' => $request->id_kontrakan,
                'id_kamar' => $request->id_kamar,
            ]);

            return redirect()->back()->with('success', 'Penyewa berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_penyewa' => 'required|string|max:255',
            'nomor_wa' => 'required|string|max:15',
            'tanggal_masuk' => 'required|date',
            'id_kontrakan' => 'required|exists:kontrakan,id',
            'id_kamar' => 'required|exists:kamar,id',
        ]);

        $penyewa = Penyewa::findOrFail($id);

        $tanggal_masuk = Carbon::parse($request->tanggal_masuk);
        $status = $tanggal_masuk->isToday() ? 'aktif' : 'tidak_aktif';

        // Cek apakah kamar sudah diisi oleh penyewa lain yang aktif
        $penyewaAktif = Penyewa::where('id_kontrakan', $request->id_kontrakan)
            ->where('id_kamar', $request->id_kamar)
            ->where('status', 'aktif')
            ->where('id', '!=', $id)
            ->exists();

        if ($penyewaAktif) {
            return redirect()->back()->with(['failed' => 'Kamar sudah diisi oleh penyewa lain yang aktif.'])->withInput();
        }

        try {
            $penyewa->update([
                'nama_penyewa' => $request->nama_penyewa,
                'nomor_wa' => $request->nomor_wa,
                'status' => $status,
                'tanggal_masuk' => $request->tanggal_masuk,
                'id_kontrakan' => $request->id_kontrakan,
                'id_kamar' => $request->id_kamar,
            ]);

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Penyewa berhasil diubah.');
        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()->with(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function putus_kontrak($id)
    {
        $penyewa = Penyewa::findOrFail($id);
        try {
            $penyewa->update([
                'status' => 'putus_kontrak',
            ]);

            return redirect()->back()->with('success', 'Penyewa berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penyewa $penyewa, $id)
    {
        try {
            Penyewa::where('id', $id)->delete();
            return response()->json(['success' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
