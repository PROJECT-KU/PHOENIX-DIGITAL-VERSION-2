<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jasa parafrase butuh DUA file dari customer:
     *  - DOCX : file kerja tim (format utuh, bisa diedit)
     *  - PDF  : acuan menghitung jumlah halaman -> harga
     * PDF->DOCX tak bisa dikonversi tanpa merusak format, jadi keduanya diminta.
     *
     * Aditif: kolom nullable. Jasa lain (cek plagiasi) hanya memakai file utama
     * seperti sebelumnya, jadi alurnya tak berubah.
     */
    public function up(): void
    {
        // Draft (sebelum bayar): file kerja mendampingi PDF penghitung halaman.
        Schema::table('jasa_draft_uploads', function (Blueprint $table) {
            $table->string('kerja_path')->nullable()->after('jumlah_halaman');
            $table->string('kerja_nama')->nullable()->after('kerja_path');
            $table->unsignedBigInteger('kerja_ukuran')->nullable()->after('kerja_nama');
            $table->string('kerja_mime')->nullable()->after('kerja_ukuran');
        });

        // Pesanan: `path` = file KERJA (dipakai tim), `pdf_path` = PDF acuan halaman.
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('mime');
            $table->string('pdf_nama')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('jasa_draft_uploads', function (Blueprint $table) {
            $table->dropColumn(['kerja_path', 'kerja_nama', 'kerja_ukuran', 'kerja_mime']);
        });

        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_nama']);
        });
    }
};
