<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perluas order_uploads: tiap baris = SATU pengecekan (file customer -> hasil
     * admin), lengkap status alur, setelan exclude Turnitin, dan persen kemiripan.
     * Murni aditif — kolom lama (path/nama_asli/ukuran/mime = file MASUK) tetap.
     */
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            // Alur pengecekan
            $table->string('status')->default('menunggu')->after('mime'); // menunggu|diproses|selesai|dibatalkan

            // File HASIL (diunggah admin)
            $table->string('hasil_path')->nullable()->after('status');
            $table->string('hasil_nama')->nullable()->after('hasil_path');
            $table->unsignedBigInteger('hasil_ukuran')->nullable()->after('hasil_nama');
            $table->string('hasil_mime')->nullable()->after('hasil_ukuran');

            // Persen kemiripan (0-100), diisi admin (pra-isi dari PDF bila terbaca)
            $table->unsignedTinyInteger('persentase')->nullable()->after('hasil_mime');

            // Setelan exclude Turnitin (default aman untuk orang tua)
            $table->boolean('exclude_bibliografi')->default(true)->after('persentase');
            $table->boolean('exclude_kutipan')->default(true)->after('exclude_bibliografi');
            $table->boolean('exclude_sumber_kecil')->default(false)->after('exclude_kutipan');
            $table->string('ambang_sumber_kecil')->nullable()->after('exclude_sumber_kecil'); // mis. "5%" / "10 kata"
            $table->text('catatan')->nullable()->after('ambang_sumber_kecil'); // permintaan khusus customer

            // Jejak waktu alur (untuk progress & audit)
            $table->timestamp('diproses_at')->nullable()->after('catatan');
            $table->timestamp('selesai_at')->nullable()->after('diproses_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'hasil_path',
                'hasil_nama',
                'hasil_ukuran',
                'hasil_mime',
                'persentase',
                'exclude_bibliografi',
                'exclude_kutipan',
                'exclude_sumber_kecil',
                'ambang_sumber_kecil',
                'catatan',
                'diproses_at',
                'selesai_at',
            ]);
        });
    }
};
