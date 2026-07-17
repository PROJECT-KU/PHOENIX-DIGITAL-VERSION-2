<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom status semula enum('active','non-active'). Diubah ke VARCHAR agar
        // bisa menampung status 'pending' (antrian moderasi) — seragam dengan
        // Ulasan Produk yang statusnya pending/approved/hidden.
        DB::statement("ALTER TABLE testimonis MODIFY status VARCHAR(20) NULL");

        // Semua non-active dijadikan 'pending' untuk ditinjau ulang admin
        // (pilihan pemilik). Setelah ini: pending=menunggu, active=disetujui,
        // non-active=ditolak.
        DB::table('testimonis')->where('status', 'non-active')->update(['status' => 'pending']);

        // ditinjau_at tak lagi diperlukan: 'pending' sudah menandai "belum
        // ditinjau" tanpa ambiguitas, jadi penanda tambahan itu dibuang.
        if (Schema::hasColumn('testimonis', 'ditinjau_at')) {
            Schema::table('testimonis', function (Blueprint $table) {
                $table->dropColumn('ditinjau_at');
            });
        }
    }

    public function down(): void
    {
        // Kembalikan pending -> non-active, lalu enum semula.
        DB::table('testimonis')->where('status', 'pending')->update(['status' => 'non-active']);
        DB::statement("ALTER TABLE testimonis MODIFY status ENUM('active','non-active') NULL");

        if (! Schema::hasColumn('testimonis', 'ditinjau_at')) {
            Schema::table('testimonis', function (Blueprint $table) {
                $table->timestamp('ditinjau_at')->nullable()->after('source');
            });
        }
    }
};
