<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\PenggunaController; // ✅ Tambahan
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

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ---------------------------------
    // PEKERJAAN - UMUM (User & Admin & Super Admin)
    // ---------------------------------
    Route::get('pekerjaan', [PekerjaanController::class, 'index'])->name('pekerjaan.index');

    // Form tambah pekerjaan → ADMIN & SUPER ADMIN
    Route::get('pekerjaan/create', [PekerjaanController::class, 'create'])
        ->name('pekerjaan.create')
        ->middleware('role:admin,super_admin');

    Route::get('pekerjaan/{pekerjaan}', [PekerjaanController::class, 'show'])->name('pekerjaan.show');

    // ---------------------------------
    // FITUR ADMIN (Admin & Super Admin)
    // ---------------------------------
    Route::middleware('role:admin,super_admin')->group(function () {

        // --- Resource Barang (penuh) ---
        Route::get('/barang/search', [BarangController::class, 'search'])->name('barang.search');
        Route::resource('barang', BarangController::class);

        // --- Barang Masuk ---
        Route::resource('barang-masuk', BarangMasukController::class)->except(['show', 'edit', 'update']);

        // --- Barang Keluar ---
        Route::get('barang-keluar', [\App\Http\Controllers\BarangKeluarController::class, 'index'])
            ->name('barang-keluar.index');
        Route::get('barang-keluar/{pekerjaan}/create', [\App\Http\Controllers\BarangKeluarController::class, 'create'])
            ->name('barang-keluar.create');
        Route::post('barang-keluar/{pekerjaan}', [\App\Http\Controllers\BarangKeluarController::class, 'store'])
            ->name('barang-keluar.store');
        Route::get('barang-keluar/{pekerjaan}/create', [BarangKeluarController::class, 'create'])->name('barang-keluar.create');
        Route::post('barang-keluar/{pekerjaan}', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');

        // --- Pekerjaan: CRUD lainnya (store, edit, update, delete) ---
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
        // --- Update & Delete Foto Transaksi ---
        Route::post('/transaksi/{transaksi}/update-foto', [PekerjaanController::class, 'updateFoto'])->name('transaksi.update-foto');
        Route::delete('/transaksi/{transaksi}/delete-foto', [PekerjaanController::class, 'deleteFoto'])->name('transaksi.delete-foto');
    }); // end role:admin,super_admin

    // ---------------------------------
    // KHUSUS SUPER ADMIN (Manajemen Pengguna)
    // ---------------------------------
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('pengguna', PenggunaController::class);
    });
    Route::patch('pengguna/{pengguna}/toggle-active', [PenggunaController::class, 'toggleActive'])
        ->name('pengguna.toggle-active');
}); // end auth