<?php

use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanTahunanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/api/ringkasan/harian', [LaporanController::class, 'get_ringkasan_harian']);
    Route::post('/api/ringkasan/bulanan', [LaporanBulananController::class, 'get_ringkasan_bulanan']);
    Route::post('/api/ringkasan/tahunan', [LaporanTahunanController::class, 'get_ringkasan_tahunan']);

    Route::get('/api/ringkasan/exportExcel/harian', [LaporanController::class, 'excel_ringkasan_harian']);
    Route::get('/api/ringkasan/exportExcel/bulanan', [LaporanBulananController::class, 'excel_ringkasan_bulanan']);
    Route::get('/api/ringkasan/exportExcel/tahunan', [LaporanTahunanController::class, 'excel_ringkasan_tahunan']);
});
