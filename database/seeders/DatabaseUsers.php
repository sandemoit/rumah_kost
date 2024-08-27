<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseUsers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
            'image' => 'default.png'
        ]);

        User::create([
            'name' => 'Karyawan',
            'email' => 'karyawan@gmail.com',
            'password' => Hash::make('karyawan'),
            'role' => 'karyawan',
        ]);
    }
}
