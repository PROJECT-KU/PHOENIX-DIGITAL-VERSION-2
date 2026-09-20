<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian kepuasan dari pelanggan + template yang sekalian mengubah tiket.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            // 1 = kecewa, 2 = biasa saja, 3 = puas.
            $table->unsignedTinyInteger('kepuasan')->nullable()->after('tunda_sampai');
            $table->timestamp('kepuasan_at')->nullable()->after('kepuasan');
            $table->text('kepuasan_komentar')->nullable()->after('kepuasan_at');
            $table->index('kepuasan');
        });

        Schema::table('customer_message_templates', function (Blueprint $table) {
            // Dipakai saat template diterapkan: "Akun sedang disiapkan" hampir
            // selalu berarti status Diproses.
            $table->string('status_baru', 20)->nullable()->after('isi');
            $table->string('kategori_baru', 40)->nullable()->after('status_baru');
        });
    }

    public function down(): void
    {
        Schema::table('customer_message_templates', function (Blueprint $table) {
            $table->dropColumn(['status_baru', 'kategori_baru']);
        });

        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex(['kepuasan']);
            $table->dropColumn(['kepuasan', 'kepuasan_at', 'kepuasan_komentar']);
        });
    }
};
