<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penyedia mana yang mengerjakan satu unggahan.
 *
 * Sejak ada penyedia cadangan, kartu di dasbor tidak boleh lagi menebak:
 * pernah tertulis "Bot sedang mengunggah ke submitin.id" padahal berkasnya
 * sudah masuk cekplagiat.id dan koinnya sudah terpotong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->string('bot_penyedia', 30)->nullable()->after('bot_tunggu_admin');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn('bot_penyedia');
        });
    }
};
