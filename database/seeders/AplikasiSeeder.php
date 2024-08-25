<?php

namespace Database\Seeders;

use App\Models\Aplikasi;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AplikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::insert([
            ['key'=>'nama_aplikasi', 
            'value'=>'Catat.biz'],
            ['key'=>'nowa' , 'value'=>'123456789'],
            ['key'=>'token' , 'value'=>'123456789'],
            ['key'=>'format_tagihan' , 'value'=>'Bayar tagihan dong {name} ke {var1}'],
            ['key'=>'logo' , 'value'=>'catatbiz.png'],
        ]);
    }
}
