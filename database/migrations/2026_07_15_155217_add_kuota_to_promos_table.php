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
        Schema::table('promos', function (Blueprint $table) {
            // Batas jumlah pemakaian promo. NULL = tanpa batas -> semua promo
            // yang sudah ada tidak berubah perilakunya sama sekali.
            //
            // Dipadu kolom `untuk_pembeli_pertama` yang sudah ada:
            //   kuota=20 + untuk_pembeli_pertama=1 -> "20 pembeli pertama"
            //   kuota=20 + untuk_pembeli_pertama=0 -> "kuota 20 untuk siapa saja"
            $table->unsignedInteger('kuota')->nullable()->after('untuk_pembeli_pertama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn('kuota');
        });
    }
};
