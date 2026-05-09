<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pekerjaans', function (Blueprint $table) {
            $table->string('created_by_name', 100)->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('pekerjaans', function (Blueprint $table) {
            $table->dropColumn('created_by_name');
        });
    }
};