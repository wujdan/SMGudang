<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PekerjaanController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════
// AUTH (Guest)
// ═══════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ═══════════════════════════════════
// PROTECTED (Harus Login)
// ═══════════════════════════════════
Route::middleware('auth')->group(function () {

    // ---------------------------------
    // UMUM (User & Admin)
    // ---------------------------------
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pekerjaan — hanya lihat daftar (index) dan detail (show)
    Route::get('pekerjaan', [PekerjaanController::class, 'index'])->name('pekerjaan.index');
    Route::get('pekerjaan/{pekerjaan}', [PekerjaanController::class, 'show'])->name('pekerjaan.show');
    // Jika user TIDAK boleh lihat detail, hapus route 'show' di atas.

    // ---------------------------------
    // KHUSUS ADMIN
    // ---------------------------------
    Route::middleware('role:admin')->group(function () {

        // --- Resource Barang (penuh) ---
        Route::get('/barang/search', [BarangController::class, 'search'])->name('barang.search');
        Route::resource('barang', BarangController::class);

        // --- Barang Masuk (tanpa show, edit, update) ---
        Route::resource('barang-masuk', BarangMasukController::class)->except(['show', 'edit', 'update']);

        // --- Barang Keluar ---
        Route::get('barang-keluar', [\App\Http\Controllers\BarangKeluarController::class, 'index'])
            ->name('barang-keluar.index');
        Route::get('barang-keluar/{pekerjaan}/create', [\App\Http\Controllers\BarangKeluarController::class, 'create'])
            ->name('barang-keluar.create');
        Route::post('barang-keluar/{pekerjaan}', [\App\Http\Controllers\BarangKeluarController::class, 'store'])
            ->name('barang-keluar.store');

        // --- Pekerjaan: CRUD selain index & show ---
        Route::get('pekerjaan/create', [PekerjaanController::class, 'create'])->name('pekerjaan.create');
        Route::post('pekerjaan', [PekerjaanController::class, 'store'])->name('pekerjaan.store');
        Route::get('pekerjaan/{pekerjaan}/edit', [PekerjaanController::class, 'edit'])->name('pekerjaan.edit');
        Route::put('pekerjaan/{pekerjaan}', [PekerjaanController::class, 'update'])->name('pekerjaan.update');
        Route::delete('pekerjaan/{pekerjaan}', [PekerjaanController::class, 'destroy'])->name('pekerjaan.destroy');

        // --- Transaksi Cart dalam Pekerjaan ---
        Route::post('/pekerjaan/{pekerjaan}/items', [PekerjaanController::class, 'addItem'])->name('pekerjaan.add-item');
        Route::post('/transaksi/{transaksi}/return', [PekerjaanController::class, 'returnItem'])->name('transaksi.return');
        Route::get('/transaksi/{transaksi}/edit', [PekerjaanController::class, 'editItem'])->name('transaksi.edit');
        Route::put('/transaksi/{transaksi}', [PekerjaanController::class, 'updateItem'])->name('transaksi.update');
        Route::delete('/transaksi/{transaksi}', [PekerjaanController::class, 'destroyItem'])->name('transaksi.delete');

        // --- Laporan ---
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
            Route::get('/masuk', [LaporanController::class, 'masuk'])->name('masuk');
            Route::get('/keluar', [LaporanController::class, 'keluar'])->name('keluar');
            Route::get('/statistik', [LaporanController::class, 'statistik'])->name('statistik');
            Route::get('/rekap', [LaporanController::class, 'rekap'])->name('rekap');
        });

    }); // end role:admin

}); // end auth