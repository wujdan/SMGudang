<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_pekerjaans', function (Blueprint $table) {
           $table->id();
            $table->string('no_transaksi')->unique();
            $table->foreignId('pekerjaan_id')->constrained('pekerjaans')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('jumlah');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->date('tanggal_keluar');
            
            // Khusus tools (peminjaman)
            $table->date('tgl_kembali_rencana')->nullable();
            $table->date('tgl_kembali_aktual')->nullable();
            $table->integer('stok_sebelum_kembali')->nullable();
            $table->enum('status_pinjam', ['dipinjam', 'dikembalikan'])->nullable(); // null = cons/material
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_pekerjaans');
    }
};
