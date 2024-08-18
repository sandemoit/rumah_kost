<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsAppHelper;
use App\Models\Setting;
use Illuminate\Http\Request;
use Monolog\Handler\SendGridHandler;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = Setting::whereIn('key', ['logo', 'nama_aplikasi', 'token', 'nowa', 'format_tagihan'])->get()->keyBy('key');

        // Inisialisasi array $data dengan nilai-nilai yang diambil
        $data = [
            'pageTitle' => 'Pengaturan Umum',
            'logo' => $settings->get('logo'),
            'nama_aplikasi' => $settings->get('nama_aplikasi'),
            'token' => $settings->get('token'),
            'nowa' => $settings->get('nowa'),
            'format_tagihan' => $settings->get('format_tagihan'),
        ];

        return view('admin.pengaturan.umum', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_aplikasi' => 'required|string',
            'nowa' => 'required|string',
            'token' => 'required|string',
            'format_tagihan' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            $value = $value ?: null;
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('setting')->with('success', 'Berhasil di Update!');
    }

    public function test_wa(Request $request)
    {
        try {
            $nowa = Setting::where('key', 'nowa')->first();
            $nowa = $nowa->value;
            $format_tagihan = Setting::where('key', 'format_tagihan')->first();
            $format_tagihan = $format_tagihan->value;

            $name = 'Roni';
            $var1 = 'Rp. 100.000';

            $target = "{$nowa}|{$name}|{$var1}";
            $message = $format_tagihan;
            $response = WhatsAppHelper::sendWhatsApp($target, $message);
            $decodedResponse = json_decode($response, true);

            if (isset($decodedResponse['status']) && $decodedResponse['status'] == 'true') {
                return redirect()->back()->with('success', 'Tagihan telah dikirim.');
            } else {
                return redirect()->back()->with('failed', $decodedResponse['reason']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
