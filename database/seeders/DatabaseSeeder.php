<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\User;
use App\Models\Pekerjaan;
use App\Models\BarangMasuk;
use App\Models\TransaksiPekerjaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama biar nggak duplikat pas di-seed ulang
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Aktifkan jika pakai MySQL
        // User::truncate();
        // Barang::truncate();
        // Pekerjaan::truncate();
        
        // 1. Create admin user
        User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gudang.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Seed barang cons (Consumables)
        $cons = [
            ['nama_barang' => 'Batu Gerinda 4"', 'satuan' => 'pcs', 'stok' => 100, 'stok_minimum' => 20],
            ['nama_barang' => 'Kawat Las RB26', 'satuan' => 'kg', 'stok' => 30, 'stok_minimum' => 5],
            ['nama_barang' => 'Isolasi Hitam', 'satuan' => 'roll', 'stok' => 25, 'stok_minimum' => 5],
            ['nama_barang' => 'Mata Bor 10mm', 'satuan' => 'pcs', 'stok' => 20, 'stok_minimum' => 5],
        ];

        foreach ($cons as $item) {
            $barang = Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('cons'),
                'kategori' => 'cons',
                'is_active' => true,
            ]));

            // Tambahkan catatan barang masuk awal
            BarangMasuk::create([
                'no_transaksi' => BarangMasuk::generateNoTransaksi(),
                'barang_id' => $barang->id,
                'jumlah' => $item['stok'],
                'stok_sebelum' => 0,
                'stok_sesudah' => $item['stok'],
                'tanggal' => now(),
                'sumber' => 'Stok Awal',
            ]);
        }

        // 3. Seed tools (Peralatan)
        $tools = [
            ['nama_barang' => 'Travo Las 200A', 'satuan' => 'unit', 'stok' => 3, 'stok_minimum' => 1],
            ['nama_barang' => 'Grinda Tangan 4"', 'satuan' => 'unit', 'stok' => 5, 'stok_minimum' => 2],
            ['nama_barang' => 'Bor Listrik', 'satuan' => 'unit', 'stok' => 4, 'stok_minimum' => 1],
        ];

        foreach ($tools as $item) {
            Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('tools'),
                'kategori' => 'tools',
                'is_active' => true,
            ]));
        }

        // 4. Seed Pekerjaan (Project)
        $pekerjaan = Pekerjaan::create([
            'kode_pekerjaan' => Pekerjaan::generateKode(),
            'nama_pekerjaan' => 'Pemasangan Ducting Area A',
            'lokasi' => 'Workshop Utama',
            'nama_peminjam' => 'Budi Santoso',
            'tanggal_mulai' => now(),
            'status' => 'aktif',
        ]);

        // 5. Seed Transaksi (Contoh peminjaman barang)
        $barangPinjam = Barang::where('kategori', 'tools')->first();
        if ($barangPinjam) {
            TransaksiPekerjaan::create([
                'no_transaksi' => TransaksiPekerjaan::generateNoTransaksi($pekerjaan->id),
                'pekerjaan_id' => $pekerjaan->id,
                'barang_id' => $barangPinjam->id,
                'jumlah' => 1,
                'stok_sebelum' => $barangPinjam->stok,
                'stok_sesudah' => $barangPinjam->stok - 1,
                'tanggal_keluar' => now(),
                'tgl_kembali_rencana' => now()->addDays(3),
                'status_pinjam' => 'dipinjam',
            ]);
            
            // Kurangi stok barang asli
            $barangPinjam->decrement('stok', 1);
        }
    }
}