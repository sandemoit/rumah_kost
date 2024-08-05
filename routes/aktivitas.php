<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/api/aktivitas/harian', [LaporanController::class, 'get_aktivitas_harian']);
});