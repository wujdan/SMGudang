<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PekerjaanController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master Barang
    Route::get('/barang/search', [BarangController::class, 'search'])->name('barang.search');
    Route::resource('barang', BarangController::class);

    // Barang Masuk
    Route::resource('barang-masuk', BarangMasukController::class)->except(['show', 'edit', 'update']);

    // Pekerjaan & Cart
    Route::resource('pekerjaan', PekerjaanController::class);
    Route::post('/pekerjaan/{pekerjaan}/items', [PekerjaanController::class, 'addItem'])->name('pekerjaan.add-item');
    Route::post('/transaksi/{transaksi}/return', [PekerjaanController::class, 'returnItem'])->name('transaksi.return');

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/masuk', [LaporanController::class, 'masuk'])->name('masuk');
        Route::get('/keluar', [LaporanController::class, 'keluar'])->name('keluar');
        Route::get('/statistik', [LaporanController::class, 'statistik'])->name('statistik');
        Route::get('/rekap', [LaporanController::class, 'rekap'])->name('rekap');
    });
});
