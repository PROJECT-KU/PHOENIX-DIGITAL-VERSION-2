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
        // badge_color hanya dipakai komponen promo-badge, dan komponen itu TIDAK
        // pernah dipanggil dari halaman mana pun — warnanya tak pernah tampil di
        // publik. Komponennya sudah dihapus, jadi kolom ini tinggal yatim.
        //
        // Yang TETAP dipakai: badge_text -> judul banner Flash Sale di homepage.
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn('badge_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->string('badge_color')->nullable()->after('badge_text');
        });
    }
};
