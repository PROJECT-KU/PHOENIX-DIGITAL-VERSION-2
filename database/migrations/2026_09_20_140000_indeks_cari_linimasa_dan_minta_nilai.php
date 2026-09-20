<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks pencarian linimasa + penanda "sudah diminta menilai".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->timestamp('minta_nilai_at')->nullable()->after('kepuasan_komentar');
        });

        // FULLTEXT hanya ada di MySQL. Uji berjalan di SQLite dan tetap memakai
        // LIKE — lihat CustomerMessageList::kueri().
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE customer_message_logs ADD FULLTEXT cml_isi_fulltext (isi)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE customer_message_logs DROP INDEX cml_isi_fulltext');
        }

        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropColumn('minta_nilai_at');
        });
    }
};
