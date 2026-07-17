<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spendings', function (Blueprint $table) {
            // Daftar path gambar/bukti (bisa lebih dari satu). Kolom lama "gambar"
            // dipertahankan sebagai gambar pertama untuk kompatibilitas thumbnail.
            $table->json('gambar_list')->nullable()->after('gambar');
        });
    }

    public function down(): void
    {
        Schema::table('spendings', function (Blueprint $table) {
            $table->dropColumn('gambar_list');
        });
    }
};
