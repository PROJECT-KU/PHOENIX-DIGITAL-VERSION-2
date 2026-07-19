<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jasa per halaman (parafrase): customer boleh mengecualikan halaman
     * tertentu (mis. cover, daftar isi, daftar pustaka). Halaman yang
     * dikecualikan TIDAK ditagih, jadi perlu dicatat untuk harga & instruksi kerja.
     * Aditif — produk non-jasa tak terpengaruh (nilai null).
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Daftar halaman yang dilewati, mis. "1,2,12" (sudah dinormalkan).
            $table->string('halaman_dikecualikan')->nullable()->after('jumlah_halaman');
            // Jumlah halaman yang benar-benar dikerjakan & ditagih.
            $table->unsignedInteger('halaman_dihitung')->nullable()->after('halaman_dikecualikan');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['halaman_dikecualikan', 'halaman_dihitung']);
        });
    }
};
