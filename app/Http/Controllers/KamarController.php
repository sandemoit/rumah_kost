<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data  = [
            'kamar' => Kamar::all(),
            'kontrakan' => Kontrakan::all(),
            'pageTitle' => 'Manajemen Kamar',
        ];
        return view('admin.data-master.kamar.kamar', $data);
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
    public function show(Kamar $kamar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kamar $kamar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kamar $kamar, $id)
    {
        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'id_kontrakan' => 'required',
        ]);

        try {
            // Buat kamar baru
            Kamar::where('id', $id)->update([
                'nama_kamar' => $request->nama_kamar,
                'id_kontrakan' => $request->id_kontrakan,
                'keterangan' => $request->keterangan
            ]);

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
    public function destroy(Kamar $kamar, $id)
    {
        try {
            Kamar::where('id', $id)->delete();
            return response()->json(['success' => 'Kamar berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['failed' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
