<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 2)->default(0)->after('jumlah');
            $table->decimal('total_nilai_masuk', 15, 2)
                ->virtualAs('jumlah * harga_satuan')
                ->after('harga_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            // Hapus kedua kolom jika ada
            $table->dropColumn(['harga_satuan', 'total_nilai_masuk']);
        });
    }
};