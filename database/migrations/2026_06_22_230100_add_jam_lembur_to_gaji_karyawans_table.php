<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->unsignedInteger('jam_lembur')->default(0)->after('uang_lembur');
        });
    }

    public function down(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->dropColumn('jam_lembur');
        });
    }
};
