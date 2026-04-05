<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gudang.com',
            'password' => Hash::make('password'),
        ]);

        // Seed barang cons
        $cons = [
            ['nama_barang' => 'Batu Gerinda 4"', 'satuan' => 'pcs', 'stok' => 100, 'stok_minimum' => 20],
            ['nama_barang' => 'Batu Gerinda 7"', 'satuan' => 'pcs', 'stok' => 50, 'stok_minimum' => 10],
            ['nama_barang' => 'Kawat Las RB26', 'satuan' => 'kg', 'stok' => 30, 'stok_minimum' => 5],
            ['nama_barang' => 'Kawat Las Stainless', 'satuan' => 'kg', 'stok' => 15, 'stok_minimum' => 3],
            ['nama_barang' => 'Isolasi Hitam', 'satuan' => 'roll', 'stok' => 25, 'stok_minimum' => 5],
            ['nama_barang' => 'Mata Bor 10mm', 'satuan' => 'pcs', 'stok' => 20, 'stok_minimum' => 5],
            ['nama_barang' => 'Elektroda Las 2.6mm', 'satuan' => 'kg', 'stok' => 40, 'stok_minimum' => 8],
            ['nama_barang' => 'Amplas Kasar', 'satuan' => 'lembar', 'stok' => 60, 'stok_minimum' => 15],
        ];

        foreach ($cons as $item) {
            Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('cons'),
                'kategori' => 'cons',
            ]));
        }

        // Seed barang material
        $materials = [
            ['nama_barang' => 'Ducting 6"', 'satuan' => 'batang', 'stok' => 50, 'stok_minimum' => 10],
            ['nama_barang' => 'Flange Ducting 6"', 'satuan' => 'pcs', 'stok' => 30, 'stok_minimum' => 6],
            ['nama_barang' => 'Pipa Galvanis 1"', 'satuan' => 'batang', 'stok' => 40, 'stok_minimum' => 8],
            ['nama_barang' => 'Elbow 90° 1"', 'satuan' => 'pcs', 'stok' => 60, 'stok_minimum' => 10],
            ['nama_barang' => 'Baut M10x30', 'satuan' => 'pcs', 'stok' => 200, 'stok_minimum' => 50],
            ['nama_barang' => 'Plat Besi 3mm', 'satuan' => 'lembar', 'stok' => 20, 'stok_minimum' => 4],
        ];

        foreach ($materials as $item) {
            Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('material'),
                'kategori' => 'material',
            ]));
        }

        // Seed tools
        $tools = [
            ['nama_barang' => 'Travo Las 200A', 'satuan' => 'unit', 'stok' => 3, 'stok_minimum' => 1],
            ['nama_barang' => 'Blander Potong', 'satuan' => 'unit', 'stok' => 4, 'stok_minimum' => 1],
            ['nama_barang' => 'Grinda Tangan 4"', 'satuan' => 'unit', 'stok' => 5, 'stok_minimum' => 2],
            ['nama_barang' => 'Grinda Tangan 7"', 'satuan' => 'unit', 'stok' => 3, 'stok_minimum' => 1],
            ['nama_barang' => 'Kunci Pas Set', 'satuan' => 'set', 'stok' => 5, 'stok_minimum' => 2],
            ['nama_barang' => 'Kunci Ring Set', 'satuan' => 'set', 'stok' => 5, 'stok_minimum' => 2],
            ['nama_barang' => 'Kunci Inggris 12"', 'satuan' => 'pcs', 'stok' => 6, 'stok_minimum' => 2],
            ['nama_barang' => 'Bor Listrik', 'satuan' => 'unit', 'stok' => 4, 'stok_minimum' => 1],
            ['nama_barang' => 'Multitester Digital', 'satuan' => 'unit', 'stok' => 3, 'stok_minimum' => 1],
            ['nama_barang' => 'Tang Kombinasi', 'satuan' => 'pcs', 'stok' => 8, 'stok_minimum' => 2],
            ['nama_barang' => 'Obeng Set', 'satuan' => 'set', 'stok' => 6, 'stok_minimum' => 2],
            ['nama_barang' => 'Palu 1kg', 'satuan' => 'pcs', 'stok' => 5, 'stok_minimum' => 2],
        ];

        foreach ($tools as $item) {
            Barang::create(array_merge($item, [
                'kode_barang' => Barang::generateKode('tools'),
                'kategori' => 'tools',
            ]));
        }
    }
}
