<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\User;
use App\Models\Pekerjaan;
use App\Models\BarangMasuk;
use App\Models\TransaksiPekerjaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin user
        User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gudang.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Barang Consumable (habis pakai)
        $cons = [
            [
                'nama_barang' => 'Batu Gerinda 4"',
                'satuan' => 'pcs',
                'stok' => 100,
                'stok_minimum' => 20,
                'prices' => 15000,
            ],
            [
                'nama_barang' => 'Kawat Las RB26',
                'satuan' => 'kg',
                'stok' => 30,
                'stok_minimum' => 5,
                'prices' => 85000,
            ],
            [
                'nama_barang' => 'Isolasi Hitam',
                'satuan' => 'roll',
                'stok' => 25,
                'stok_minimum' => 5,
                'prices' => 12500,
            ],
            [
                'nama_barang' => 'Mata Bor 10mm',
                'satuan' => 'pcs',
                'stok' => 20,
                'stok_minimum' => 5,
                'prices' => 28000,
            ],
        ];

        foreach ($cons as $item) {
            $barang = Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('cons'),
                'kategori' => 'cons',
                'is_active' => true,
            ]));

            // Catatan stok awal masuk
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

        // 3. Barang Tools (pinjam)
        $tools = [
            [
                'nama_barang' => 'Travo Las 200A',
                'satuan' => 'unit',
                'stok' => 3,
                'stok_minimum' => 1,
                'prices' => 5000000,
            ],
            [
                'nama_barang' => 'Grinda Tangan 4"',
                'satuan' => 'unit',
                'stok' => 5,
                'stok_minimum' => 2,
                'prices' => 750000,
            ],
            [
                'nama_barang' => 'Bor Listrik',
                'satuan' => 'unit',
                'stok' => 4,
                'stok_minimum' => 1,
                'prices' => 1200000,
            ],
        ];

        foreach ($tools as $item) {
            Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('tools'),
                'kategori' => 'tools',
                'is_active' => true,
            ]));
        }

        // 4. Data Pekerjaan (Project)
        $pekerjaan = Pekerjaan::create([
            'kode_pekerjaan' => Pekerjaan::generateKode(),
            'nama_pekerjaan' => 'Pemasangan Ducting Area A',
            'lokasi' => 'Workshop Utama',
            'nama_peminjam' => 'Budi Santoso',
            'tanggal_mulai' => now(),
            'status' => 'aktif',
        ]);

        // 5. Contoh transaksi peminjaman tools
        $barangPinjam = Barang::where('kategori', 'tools')->where('nama_barang', 'Grinda Tangan 4"')->first();
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

            // Update stok barang
            $barangPinjam->decrement('stok', 1);
        }
    }
}