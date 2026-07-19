<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Produk JASA yang butuh customer mengunggah dokumen (mis. cek plagiasi).
            // Default false -> produk lama (akun) tak terpengaruh.
            $table->boolean('butuh_file')->default(false)->after('tipe_akun');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('butuh_file');
        });
    }
};
