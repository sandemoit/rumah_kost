<?php

namespace App\Http\Controllers;

use App\Models\Kontrakan;
use Illuminate\Http\Request;

class KontrakanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data  = [
            'kontrakan' => Kontrakan::all(),
            'pageTitle' => 'Manajemen Kontrakan',
        ];
        return view('admin.data-master.kontrakan.kontrakan', $data);
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
}
