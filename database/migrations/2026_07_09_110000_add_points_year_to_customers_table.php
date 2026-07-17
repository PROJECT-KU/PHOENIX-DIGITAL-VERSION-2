<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Tahun kepemilikan poin. Poin kadaluarsa/di-reset tiap akhir tahun
            // kalender, jadi poin dengan points_year < tahun berjalan dianggap habis.
            $table->unsignedSmallInteger('points_year')->nullable()->after('point_balance');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('points_year');
        });
    }
};
