<?php

use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanTahunanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/api/aktivitas/harian', [LaporanController::class, 'get_aktivitas_harian']);
    Route::post('/api/aktivitas/bulanan', [LaporanBulananController::class, 'get_aktivitas_bulanan']);
    Route::post('/api/aktivitas/tahunan', [LaporanTahunanController::class, 'get_aktivitas_tahunan']);

    Route::get('/laporan/harian/aktivitas/exportExcel', [LaporanController::class, 'excel_aktivitas_harian']);
    Route::get('/laporan/bulan/aktivitas/exportExcel', [LaporanBulananController::class, 'excel_aktivitas_bulan']);
    Route::get('/laporan/tahun/aktivitas/exportExcel', [LaporanTahunanController::class, 'excel_aktivitas_tahun']);
});
