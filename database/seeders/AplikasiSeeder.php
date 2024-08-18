<?php

namespace Database\Seeders;

use App\Models\Aplikasi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AplikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Aplikasi::create([
            'nama_aplikasi' => 'Catat.biz',
            'nowa' => '123456789',
            'token' => '123456789',
            'format_tagihan' => 'Bayar tagihan dong {name} ke {var1}',
            'logo' => 'catatbiz.png',
        ]);
    }
}
