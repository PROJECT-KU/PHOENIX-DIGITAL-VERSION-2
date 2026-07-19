<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu pengecekan bisa punya 3 berkas hasil (terutama jasa parafrase):
     *  - hasil_*      : hasil cek PLAGIASI (Turnitin) — kolom lama, tak berubah
     *  - hasil_ai_*   : hasil cek AI + persentasenya
     *  - hasil_docx_* : dokumen DOCX hasil parafrase yang dikerjakan tim
     *
     * Aditif & nullable: jasa cek plagiasi yang hanya memakai hasil_* tetap
     * berjalan seperti sebelumnya.
     */
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            // Hasil cek AI
            $table->string('hasil_ai_path')->nullable()->after('persentase');
            $table->string('hasil_ai_nama')->nullable()->after('hasil_ai_path');
            $table->unsignedBigInteger('hasil_ai_ukuran')->nullable()->after('hasil_ai_nama');
            $table->string('hasil_ai_mime')->nullable()->after('hasil_ai_ukuran');
            $table->unsignedTinyInteger('persentase_ai')->nullable()->after('hasil_ai_mime');

            // Dokumen hasil parafrase (DOCX)
            $table->string('hasil_docx_path')->nullable()->after('persentase_ai');
            $table->string('hasil_docx_nama')->nullable()->after('hasil_docx_path');
            $table->unsignedBigInteger('hasil_docx_ukuran')->nullable()->after('hasil_docx_nama');
            $table->string('hasil_docx_mime')->nullable()->after('hasil_docx_ukuran');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn([
                'hasil_ai_path', 'hasil_ai_nama', 'hasil_ai_ukuran', 'hasil_ai_mime', 'persentase_ai',
                'hasil_docx_path', 'hasil_docx_nama', 'hasil_docx_ukuran', 'hasil_docx_mime',
            ]);
        });
    }
};
