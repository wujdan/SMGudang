<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->decimal('prices', 15, 2)->nullable()->after('nama_barang');
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {

            if (Schema::hasColumn('barangs', 'prices')) {
                $table->dropColumn('prices');
            }

        });
    }
};