<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tipe akun produk: sharing (1 akun banyak orang) / private (1 akun 1 orang)
        Schema::table('products', function (Blueprint $table) {
            $table->enum('tipe_akun', ['sharing', 'private'])->default('sharing')->after('nama_akun');
        });

        // Durasi pada pengeluaran pembelian akun.
        // - Produk PRIVATE: entri = "modal satuan per durasi" (katalog), nominal = modal 1 akun.
        // - Produk SHARING: durasi null, nominal = total (perilaku lama).
        Schema::table('spendings', function (Blueprint $table) {
            $table->unsignedSmallInteger('durasi_value')->nullable()->after('product_id');
            $table->enum('durasi_type', ['bulan', 'tahun'])->nullable()->after('durasi_value');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tipe_akun');
        });
        Schema::table('spendings', function (Blueprint $table) {
            $table->dropColumn(['durasi_value', 'durasi_type']);
        });
    }
};
