<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use Illuminate\Http\Request;

class KontrakanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kontrakans = Kontrakan::all();

        // Hitung jumlah kamar untuk setiap kontrakan
        foreach ($kontrakans as $kontrakan) {
            $kontrakan->count_kamar = Kamar::where('id_kontrakan', $kontrakan->id)->count();
        }

        $data = [
            'kontrakan' => $kontrakans,
            'pageTitle' => 'Manajemen Kontrakan',
        ];

        return view('admin.data-master.kontrakan.kontrakan', $data);
    }

    public function detail($nama_kontrakan)
    {
        // Temukan kontrakan berdasarkan nama_kontrakan
        $kontrakan = Kontrakan::where('nama_kontrakan', $nama_kontrakan)->firstOrFail();

        // Temukan kamar yang terkait dengan id_kontrakan yang ditemukan
        $kamar = Kamar::where('id_kontrakan', $kontrakan->id)->get();

        $data = [
            'kamar' => $kamar,
            'kontrakan' => $kontrakan, // Menggunakan variabel $kontrakan langsung
            'pageTitle' => "Detail Kontrakan: " . $kontrakan->nama_kontrakan,
        ];

        return view('admin.data-master.kontrakan.detail', $data);
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
        $request->validate([
            'namaKontrakan' => 'required|string|max:255',
            'alamatKontrakan' => 'required|string|max:255',
        ]);

        try {
            // Buat kontrakan baru
            Kontrakan::create([
                'nama_kontrakan' => $request->namaKontrakan,
                'alamat_kontrakan' => $request->alamatKontrakan,
            ]);

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Kontrakan berhasil dibuat.');
        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()->withErrors(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function store_kamar(Request $request)
    {
        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'id_kontrakan' => 'required',
        ]);

        try {
            // Buat kamar baru
            Kamar::create([
                'nama_kamar' => $request->nama_kamar,
                'id_kontrakan' => $request->id_kontrakan,
                'keterangan' => $request->keterangan
            ]);

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Kamar berhasil dibuat.');
        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()->withErrors(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Kontrakan $kontrakan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kontrakan $kontrakan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kontrakan $kontrakan, $id)
    {
        // Validasi data
        $validatedData = $request->validate([
            'namaKontrakan' => 'required|string|max:255',
            'alamatKontrakan' => 'required|string|max:255',
        ]);

        // Temukan kategori berdasarkan ID
        $kontrakan = Kontrakan::findOrFail($id);

        // Update kategori dengan data yang divalidasi
        $kontrakan->nama_kontrakan = $validatedData['namaKontrakan'];
        $kontrakan->alamat_kontrakan = $validatedData['alamatKontrakan'];

        try {
            // Simpan perubahan
            $kontrakan->save();
            // Return response success
            return redirect()->back()->with('success', 'Kontrakan berhasil diubah.');
        } catch (\Exception $e) {
            // Return response error
            return redirect()->back()->with('success', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update_kamar(Request $request, $nama_kontrakan, $id)
    {
        // Temukan kontrakan berdasarkan nama_kontrakan
        $kontrakan = Kontrakan::where('nama_kontrakan', $nama_kontrakan)->firstOrFail();

        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        try {
            // Cari kamar yang akan diupdate berdasarkan id_kontrakan dan id kamar
            $kamar = Kamar::where('id', $id)->where('id_kontrakan', $kontrakan->id)->firstOrFail();

            // Update data kamar
            $kamar->nama_kamar = $request->nama_kamar;
            $kamar->keterangan = $request->keterangan;
            $kamar->save();

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Kamar berhasil diubah.');
        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()->withErrors(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kontrakan $kontrakan, $id)
    {

        try {
            Kontrakan::where('id', $id)->delete();
            return response()->json(['success' => 'Kontrakan berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy_kamar(Kamar $kamar, $nama_kontrakan, $id)
    {
        $kontrakan = Kontrakan::where('nama_kontrakan', $nama_kontrakan)->firstOrFail();

        try {
            Kamar::where('id', $id)->where('id_kontrakan', $kontrakan->id)->delete();
            return response()->json(['success' => 'Kamar berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
