<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/laporan/harian', [LaporanController::class, 'harian'])->name('laporan.harian');
    Route::get('/getAllBukuKas', [LaporanController::class, 'getAllBukuKas'])->name('getAllBukuKas');
    Route::get('/getAllExIn', [LaporanController::class, 'getAllExIn'])->name('getAllExIn');
    Route::get('/getAktivitas', [LaporanController::class, 'getAktivitas'])->name('getAktivitas');

    Route::get('/laporan/harian/umum', [LaporanController::class, 'umum'])->name('laporan.harian.umum');
    Route::get('/laporan/harian/aktivitas', [LaporanController::class, 'aktivitas'])->name('laporan.harian.aktivitas');
    Route::get('/laporan/harian/ringkasan', [LaporanController::class, 'ringkasan'])->name('laporan.harian.ringkasan');
});
