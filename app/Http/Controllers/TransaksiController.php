<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show($code_kontrakan)
    {
        $kontrakan = Kontrakan::where('code_kontrakan', $code_kontrakan)->firstOrFail();
        $countPintu = Kamar::where('id_kontrakan', $kontrakan->id)->count();

        $data = [
            'pageTitle' => $kontrakan->nama_kontrakan,
            'keterangan' => "Kontrakan $kontrakan->nama_kontrakan $countPintu Pintu",
            'kontrakan' => $kontrakan
        ];

        // Lakukan sesuatu dengan $kontrakan, seperti mengambil transaksi terkait
        // ...

        return view('admin.transaksi.input', $data);
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaksi $transaksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaksi $transaksi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaksi $transaksi)
    {
        //
    }
}
