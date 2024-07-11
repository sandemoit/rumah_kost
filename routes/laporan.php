<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/laporan/harian', [LaporanController::class, 'index'])->name('laporan.harian');
    Route::get('/laporan/harian/umum', [LaporanController::class, 'index'])->name('laporan.harian.umum');
    Route::get('/laporan/harian/aktivitas', [LaporanController::class, 'index'])->name('laporan.harian.aktivitas');
});
