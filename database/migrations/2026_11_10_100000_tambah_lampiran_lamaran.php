<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Berkas pendukung lamaran (opsional): portofolio, sertifikat, transkrip, dsb.
 * Satu PDF, disimpan di disk privat seperti CV & surat lamaran. Murni tambahan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_job_applications', function (Blueprint $table) {
            $table->string('lampiran_path')->nullable()->after('cover_letter_path');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_job_applications', function (Blueprint $table) {
            $table->dropColumn('lampiran_path');
        });
    }
};
