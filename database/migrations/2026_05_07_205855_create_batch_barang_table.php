<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('batch_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained()->onDelete('cascade');
            $table->string('no_transaksi_masuk');
            $table->date('tanggal_masuk');
            $table->integer('qty_awal');
            $table->integer('qty_sisa');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_nilai', 15, 2)->virtualAs('qty_awal * harga_satuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_barang');
    }
};
