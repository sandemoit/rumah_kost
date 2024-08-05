<?php

use App\Http\Controllers\AplikasiController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontrakanController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\PenyewaController;
use App\Http\Controllers\TransaksiController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/usermanajemen', [ManajemenController::class, 'index'])->name('usermanajemen');
    Route::get('/usermanajemen/create', [ManajemenController::class, 'create'])->name('usermanajemen.create');
    Route::post('/usermanajemen/create', [ManajemenController::class, 'store'])->name('usermanajemen.store');
    Route::delete('/usermanajemen/{id}', [ManajemenController::class, 'destroy'])->name('usermanajemen.destroy');
    Route::get('/usermanajemen/{id}', [ManajemenController::class, 'edit'])->name('usermanajemen.edit');
    Route::put('/usermanajemen/{id}', [ManajemenController::class, 'update'])->name('usermanajemen.update');

    Route::get('/cashcategory', [CategoryController::class, 'index'])->name('cashcategory');
    Route::post('/cashcategory', [CategoryController::class, 'store'])->name('cashcategory.store');
    Route::patch('/cashcategory/{id}', [CategoryController::class, 'update'])->name('cashcategory.update');
    Route::delete('/cashcategory/{id}', [CategoryController::class, 'destroy'])->name('cashcategory.destroy');

    Route::get('/kontrakan', [KontrakanController::class, 'index'])->name('kontrakan');
    Route::get('/kontrakan/{nama_kontrakan}', [KontrakanController::class, 'detail'])->name('kontrakan.detail');
    Route::post('/kontrakan', [KontrakanController::class, 'store'])->name('kontrakan.store');
    Route::post('/kontrakan/{nama_kontrakan}', [KontrakanController::class, 'store_kamar'])->name('kontrakan.store_kamar');
    Route::put('/kontrakan/{id}', [KontrakanController::class, 'update'])->name('kontrakan.update');
    Route::put('/kontrakan/{nama_kontrakan}/{id}', [KontrakanController::class, 'update_kamar'])->name('kontrakan.update_kamar');
    Route::delete('/kontrakan/{id}', [KontrakanController::class, 'destroy'])->name('kontrakan.destroy');
    Route::delete('/kontrakan/{nama_kontrakan}/{id}', [KontrakanController::class, 'destroy_kamar'])->name('kontrakan.destroy_kamar');

    Route::get('/penyewa', [PenyewaController::class, 'index'])->name('penyewa');
    Route::get('/get-kamar/{id}', [PenyewaController::class, 'getKamarByKontrakan'])->name('get-kamar');
    Route::post('/penyewa', [PenyewaController::class, 'store'])->name('penyewa.store');
    Route::put('/penyewa/{id}', [PenyewaController::class, 'update'])->name('penyewa.update');
    Route::delete('/penyewa/{id}', [PenyewaController::class, 'destroy'])->name('penyewa.destroy');
    Route::get('/penyewa/{id}', [PenyewaController::class, 'putus_kontrak'])->name('penyewa.putus_kontrak');
    Route::get('/penyewa/send_wa/{id}', [PenyewaController::class, 'wa_tagihan'])->name('penyewa.wa_tagihan');

    Route::get('/transaksi/{code_kontrakan}', [TransaksiController::class, 'show'])->name('transaksi.kontrakan');
    Route::get('/getKamarData/{id}', [TransaksiController::class, 'getKamarData'])->name('getKamarData');
    Route::get('/getTunggakan/{id}', [TransaksiController::class, 'getTunggakan'])->name('getTunggakan');
    Route::post('/transaksi-masuk', [TransaksiController::class, 'store_masuk'])->name('getKamarData.store_masuk');
    Route::post('/transaksi-keluar', [TransaksiController::class, 'store_keluar'])->name('getKamarData.store_keluar');
    Route::get('/api/get-transaction/{type}/{id}', [TransaksiController::class, 'getTransaction'])->name('get-transaction');
    Route::delete('/transaksi-masuk/delete/{id}', [TransaksiController::class, 'deleteMasuk'])->name('transaksi-masuk.delete');
    Route::delete('/transaksi-keluar/delete/{id}', [TransaksiController::class, 'deleteKeluar'])->name('transaksi-keluar.delete');
    Route::get('/getSaldoKontrakan/{code_kontrakan}', [TransaksiController::class, 'getSaldoKontrakan'])->name('getSaldoKontrakan');
    Route::put('/transaksi-masuk/update/{id}', [TransaksiController::class, 'update_masuk']);
    Route::put('/transaksi-keluar/update/{id}', [TransaksiController::class, 'update_keluar']);

    Route::get('/aplikasi', [AplikasiController::class, 'index'])->name('aplikasi');
});

require __DIR__ . '/laporan.php';
require __DIR__ . '/aktivitas.php';
