<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_pekerjaans', function (Blueprint $table) {
            $table->decimal('hpp_satuan', 15, 2)->nullable()->after('jumlah');
            $table->decimal('total_hpp', 15, 2)->nullable()->after('hpp_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_pekerjaans', function (Blueprint $table) {
            $table->dropColumn(['hpp_satuan', 'total_hpp']);
        });
    }
};