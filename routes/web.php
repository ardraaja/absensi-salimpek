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
    Route::post('/pegawai/store', [AdminController::class, 'store'])->name('admin.pegawai.store');
    Route::post('/pegawai/{id}/update', [AdminController::class, 'update'])->name('admin.pegawai.update');
    Route::delete('/pegawai/{id}/delete', [AdminController::class, 'destroy'])->name('admin.pegawai.destroy');
    Route::post('/update-lokasi-kantor', [AdminController::class, 'updateLokasiKantor'])->name('admin.updateLokasi');
    Route::post('/update-jam-kerja', [AdminController::class, 'updateJamKerja'])->name('admin.updateJamKerja');
    Route::post('/izin/{id}/setujui', [AdminController::class, 'setujuiIzin'])->name('admin.izin.setujui');
    Route::post('/izin/{id}/tolak', [AdminController::class, 'tolakIzin'])->name('admin.izin.tolak');
    Route::post('/izin/manual', [AdminController::class, 'tambahIzinManual'])->name('admin.izin.manual');
    Route::post('/absen/{id}/update', [AdminController::class, 'updateAbsenManual'])->name('admin.absen.update');
    Route::get('/laporan/cetak', [AdminController::class, 'cetakLaporan'])->name('admin.laporan.cetak');
    Route::post('/laporan/penandatangan', [AdminController::class, 'updatePenandatangan'])->name('admin.laporan.penandatangan');
});

// Rute khusus Pegawai
Route::middleware(['auth', 'pegawai'])->prefix('pegawai')->group(function () {
    Route::get('/dashboard', [PegawaiController::class, 'index']);
    Route::post('/absen', [PegawaiController::class, 'store']);
    Route::post('/izin/ajukan', [PegawaiController::class, 'ajukanIzin'])->name('pegawai.izin.ajukan');
});