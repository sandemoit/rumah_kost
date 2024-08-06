<?php

use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanTahunanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/getAllBukuKas', [LaporanController::class, 'getAllBukuKas'])->name('getAllBukuKas');
    Route::get('/getAllExIn', [LaporanController::class, 'getAllExIn'])->name('getAllExIn');
    Route::get('/laporan/harian', [LaporanController::class, 'harian'])->name('laporan.harian');
    Route::get('/laporan/harian/umum', [LaporanController::class, 'umum'])->name('laporan.harian.umum');
    Route::get('/laporan/harian/aktivitas', [LaporanController::class, 'aktivitas'])->name('laporan.harian.aktivitas');
    Route::get('/laporan/harian/ringkasan', [LaporanController::class, 'ringkasan'])->name('laporan.harian.ringkasan');

    Route::get('/bulanan/getAllBukuKas', [LaporanBulananController::class, 'getAllBukuKas'])->name('bulanan.getAllBukuKas');
    Route::get('/bulanan/getAllExIn', [LaporanBulananController::class, 'getAllExIn'])->name('bulanan.getAllExIn');
    Route::get('/laporan/bulanan', [LaporanBulananController::class, 'bulanan'])->name('laporan.bulanan');
    Route::get('/laporan/bulanan/umum', [LaporanBulananController::class, 'umum'])->name('laporan.bulanan.umum');
    Route::get('/laporan/bulanan/aktivitas', [LaporanBulananController::class, 'aktivitas'])->name('laporan.bulanan.aktivitas');
    Route::get('/laporan/bulanan/ringkasan', [LaporanBulananController::class, 'ringkasan'])->name('laporan.bulanan.ringkasan');

    Route::get('/tahunan/getAllBukuKas', [LaporanTahunanController::class, 'getAllBukuKas'])->name('tahunan.getAllBukuKas');
    Route::get('/tahunan/getAllExIn', [LaporanTahunanController::class, 'getAllExIn'])->name('tahunan.getAllExIn');
    Route::get('/laporan/tahunan', [LaporanTahunanController::class, 'tahunan'])->name('laporan.tahunan');
    Route::get('/laporan/tahunan/umum', [LaporanTahunanController::class, 'umum'])->name('laporan.tahunan.umum');
    Route::get('/laporan/tahunan/aktivitas', [LaporanTahunanController::class, 'aktivitas'])->name('laporan.tahunan.aktivitas');
    Route::get('/laporan/tahunan/ringkasan', [LaporanTahunanController::class, 'ringkasan'])->name('laporan.tahunan.ringkasan');
});
