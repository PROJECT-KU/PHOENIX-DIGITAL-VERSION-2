<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengembalians', function (Blueprint $table) {
            // Menandai pengembalian yang berasal dari potongan gaji karyawan
            $table->uuid('source_gaji_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pengembalians', function (Blueprint $table) {
            $table->dropColumn('source_gaji_id');
        });
    }
};
