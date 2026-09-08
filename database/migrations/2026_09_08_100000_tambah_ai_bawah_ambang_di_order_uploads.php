<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menandai laporan AI Turnitin yang persennya SENGAJA tidak disebutkan.
 *
 * Turnitin menulis "*% detected as AI" bila skornya di bawah 20%, dengan
 * alasannya sendiri: "scores below the 20% threshold are not surfaced because
 * they have a higher likelihood of false positives".
 *
 * Jadi bintang itu bukan angka yang tersembunyi, melainkan penolakan menyebut
 * angka. Memaksanya jadi bilangan — 0 atau tebakan seperti 8 — mengarang
 * ketelitian yang tidak ada, dan pelanggan yang membuka PDF-nya akan melihat
 * angka kita berbeda dari laporan aslinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->boolean('ai_bawah_ambang')->default(false)->after('persentase_ai');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn('ai_bawah_ambang');
        });
    }
};
