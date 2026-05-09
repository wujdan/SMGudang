<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('transaksi_pekerjaans', function (Blueprint $table) {
            $table->string('created_by_name', 100)->nullable()->after('keterangan');
        });
    }

    public function down()
    {
        Schema::table('transaksi_pekerjaans', function (Blueprint $table) {
            $table->dropColumn('created_by_name');
        });
    }
};
