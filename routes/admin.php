<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\ProfileController;

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
    Route::put('/cashcategory/{id}', [CategoryController::class, 'update'])->name('cashcategory.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
