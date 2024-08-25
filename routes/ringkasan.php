<?php

use App\Http\Controllers\ExportRingkasanController;
use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanCustomController;
use App\Http\Controllers\LaporanTahunanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/api/ringkasan/harian', [LaporanController::class, 'get_ringkasan_harian']);
    Route::post('/api/ringkasan/bulanan', [LaporanBulananController::class, 'get_ringkasan_bulanan']);
    Route::post('/api/ringkasan/tahunan', [LaporanTahunanController::class, 'get_ringkasan_tahunan']);
    Route::post('/api/ringkasan/custom', [LaporanCustomController::class, 'get_ringkasan_custom']);

    Route::get('/ringkasan/harian/export', [ExportRingkasanController::class, 'harian'])->name('laporan.harian.ringkasan.exportExcel');
    Route::get('/ringkasan/bulanan/export', [ExportRingkasanController::class, 'bulanan'])->name('laporan.bulanan.ringkasan.exportExcel');
    Route::get('/ringkasan/tahun/export', [LaporanTahunanController::class, 'excel_ringkasan_tahun']);

    Route::get('/laporan/export', [ExportRingkasanController::class, 'exportExcel'])->name('laporan.export');
});
