<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Status 'draft' hanya ditambahkan ke enum pada MySQL (lihat migrasi
 * 2026_06_30_120000). Komentar di sana menyebut SQLite tidak memaksakan ENUM —
 * itu keliru: Laravel menerjemahkan enum menjadi CHECK constraint, dan SQLite
 * menegakkannya. Akibatnya alur pesanan draft tidak pernah bisa diuji otomatis.
 *
 * Kolom dilonggarkan menjadi string HANYA di luar MySQL. Skema produksi tidak
 * tersentuh: di MySQL migrasi ini tidak melakukan apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        // Sengaja dibiarkan: mengembalikan CHECK constraint akan menolak baris
        // berstatus 'draft' yang sudah ada.
    }
};
