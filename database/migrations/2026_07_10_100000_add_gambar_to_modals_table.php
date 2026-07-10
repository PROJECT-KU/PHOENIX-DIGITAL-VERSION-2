<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modals', function (Blueprint $table) {
            // Bukti top-up modal (bisa lebih dari satu). "gambar" = gambar pertama
            // (kompatibilitas thumbnail), "gambar_list" = seluruh daftar path.
            $table->string('gambar')->nullable()->after('deskripsi');
            $table->json('gambar_list')->nullable()->after('gambar');
        });
    }

    public function down(): void
    {
        Schema::table('modals', function (Blueprint $table) {
            $table->dropColumn(['gambar', 'gambar_list']);
        });
    }
};
