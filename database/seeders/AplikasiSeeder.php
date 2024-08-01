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
            'nama_aplikasi' => 'Catat Biz',
            'nama_perusahaan' => 'Catat Biz',
            'logo' => 'catatbiz.png',
        ]);
    }
}
