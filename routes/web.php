<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pegawai\PegawaiController;
use App\Http\Controllers\Admin\AdminController;

Route::get('/', function () {
    return redirect('/login');
});

// Rute Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute khusus Admin (Sudah disatukan ke dalam grup middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// Rute khusus Pegawai
Route::middleware(['auth', 'pegawai'])->prefix('pegawai')->group(function () {
    Route::get('/dashboard', [PegawaiController::class, 'index']);
    Route::post('/absen', [PegawaiController::class, 'store']);
});