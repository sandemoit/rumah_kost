<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KontrakanController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\PenyewaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransaksiController;

Route::middleware('auth')->group(function () {
    Route::get('transaksi/{code_kontrakan}', [TransaksiController::class, 'show'])->name('transaksi.kontrakan');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
