<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BatchBarang;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gudang.com',
            'password' => Hash::make('password'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barangList = [

            // TOOLS
            [
                'nama_barang' => 'Bor Bosch',
                'kategori' => 'tools',
                'satuan' => 'pcs',
                'stok_minimum' => 1,
                'prices' => 850000,
            ],

            [
                'nama_barang' => 'Tang Kombinasi',
                'kategori' => 'tools',
                'satuan' => 'pcs',
                'stok_minimum' => 2,
                'prices' => 75000,
            ],

            [
                'nama_barang' => 'Obeng Set',
                'kategori' => 'tools',
                'satuan' => 'set',
                'stok_minimum' => 2,
                'prices' => 120000,
            ],

            // MATERIAL
            [
                'nama_barang' => 'Kabel NYM 2x1.5',
                'kategori' => 'material',
                'satuan' => 'roll',
                'stok_minimum' => 5,
                'prices' => 450000,
            ],

            [
                'nama_barang' => 'Pipa PVC',
                'kategori' => 'material',
                'satuan' => 'batang',
                'stok_minimum' => 10,
                'prices' => 65000,
            ],

            // CONSUMABLE
            [
                'nama_barang' => 'Isolasi Listrik',
                'kategori' => 'cons',
                'satuan' => 'pcs',
                'stok_minimum' => 10,
                'prices' => 12000,
            ],

            [
                'nama_barang' => 'Cable Tie',
                'kategori' => 'cons',
                'satuan' => 'pack',
                'stok_minimum' => 15,
                'prices' => 18000,
            ],
        ];

        $barangs = [];

        foreach ($barangList as $item) {

            $barang = Barang::create([
                'kode_barang' => Barang::generateKode($item['kategori']),
                'nama_barang' => $item['nama_barang'],
                'kategori' => $item['kategori'],
                'satuan' => $item['satuan'],
                'stok' => 0,
                'stok_minimum' => $item['stok_minimum'],
                'prices' => $item['prices'],
                'is_active' => true,
                'keterangan' => 'Seeder data barang',
            ]);

            $barangs[] = $barang;
        }

        /*
        |--------------------------------------------------------------------------
        | BARANG MASUK + FIFO BATCH
        |--------------------------------------------------------------------------
        */

        foreach ($barangs as $barang) {

            for ($i = 1; $i <= 3; $i++) {

                $qty = rand(5, 25);

                $harga = $barang->prices + rand(-10000, 50000);

                $stokSebelum = $barang->stok;
                $stokSesudah = $stokSebelum + $qty;

                $tanggal = now()->subDays(rand(10, 90));

                $barangMasuk = BarangMasuk::create([
                    'no_transaksi' => BarangMasuk::generateNoTransaksi(),
                    'barang_id' => $barang->id,
                    'jumlah' => $qty,
                    'harga_satuan' => $harga,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'tanggal' => $tanggal,
                    'sumber' => 'Supplier Seeder',
                    'keterangan' => 'Seeder barang masuk',
                ]);

                BatchBarang::create([
                    'barang_id' => $barang->id,
                    'no_transaksi_masuk' => $barangMasuk->no_transaksi,
                    'tanggal_masuk' => $tanggal,
                    'qty_awal' => $qty,
                    'qty_sisa' => $qty,
                    'harga_satuan' => $harga,
                ]);

                $barang->update([
                    'stok' => $stokSesudah
                ]);

                $barang->refresh();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PEKERJAAN
        |--------------------------------------------------------------------------
        */

        $pekerjaanData = [

            [
                'nama_pekerjaan' => 'Instalasi CCTV Gedung A',
                'lokasi' => 'Surabaya',
                'nama_peminjam' => 'Budi',
                'status' => 'aktif',
            ],

            [
                'nama_pekerjaan' => 'Perbaikan Panel Listrik',
                'lokasi' => 'Sidoarjo',
                'nama_peminjam' => 'Andi',
                'status' => 'aktif',
            ],

            [
                'nama_pekerjaan' => 'Maintenance Gudang',
                'lokasi' => 'Gresik',
                'nama_peminjam' => 'Rizal',
                'status' => 'selesai',
            ],
        ];

        $pekerjaans = [];

        foreach ($pekerjaanData as $item) {

            $pekerjaan = Pekerjaan::create([
                'kode_pekerjaan' => Pekerjaan::generateKode(),
                'nama_pekerjaan' => $item['nama_pekerjaan'],
                'lokasi' => $item['lokasi'],
                'nama_peminjam' => $item['nama_peminjam'],
                'tanggal_mulai' => now()->subDays(rand(5, 30)),
                'tanggal_selesai' => $item['status'] === 'selesai'
                    ? now()->subDays(rand(1, 4))
                    : null,
                'status' => $item['status'],
                'keterangan' => 'Seeder pekerjaan',
            ]);

            $pekerjaans[] = $pekerjaan;
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI PEKERJAAN (FIFO)
        |--------------------------------------------------------------------------
        */

        foreach ($pekerjaans as $pekerjaan) {

            $randomBarangs = Barang::inRandomOrder()->take(4)->get();

            foreach ($randomBarangs as $barang) {

                $jumlah = rand(1, 3);

                if ($barang->stok < $jumlah) {
                    continue;
                }

                $batch = $barang->batchAktif()->first();

                if (!$batch) {
                    continue;
                }

                $stokSebelum = $barang->stok;
                $stokSesudah = $stokSebelum - $jumlah;

                $hppSatuan = $batch->harga_satuan;
                $totalHpp = $hppSatuan * $jumlah;

                $statusPinjam = null;
                $tglRencana = null;
                $tglAktual = null;

                /*
                |--------------------------------------------------------------------------
                | KHUSUS TOOLS
                |--------------------------------------------------------------------------
                */

                if ($barang->kategori === 'tools') {

                    $statusPinjam = rand(0, 1)
                        ? 'dipinjam'
                        : 'dikembalikan';

                    $tglRencana = now()->addDays(rand(3, 10));

                    if ($statusPinjam === 'dikembalikan') {
                        $tglAktual = now()->subDays(rand(1, 3));
                    }
                }

                TransaksiPekerjaan::create([
                    'no_transaksi' => TransaksiPekerjaan::generateNoTransaksi($pekerjaan->id),

                    'pekerjaan_id' => $pekerjaan->id,
                    'barang_id' => $barang->id,

                    'jumlah' => $jumlah,

                    'hpp_satuan' => $hppSatuan,
                    'total_hpp' => $totalHpp,

                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,

                    'tanggal_keluar' => now()->subDays(rand(1, 10)),

                    'tgl_kembali_rencana' => $tglRencana,
                    'tgl_kembali_aktual' => $tglAktual,

                    'stok_sebelum_kembali' => null,

                    'status_pinjam' => $statusPinjam,

                    'keterangan' => 'Seeder transaksi pekerjaan',
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE STOK BARANG
                |--------------------------------------------------------------------------
                */

                $barang->update([
                    'stok' => $stokSesudah
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE FIFO BATCH
                |--------------------------------------------------------------------------
                */

                $batch->update([
                    'qty_sisa' => max(0, $batch->qty_sisa - $jumlah)
                ]);
            }
        }
    }
}