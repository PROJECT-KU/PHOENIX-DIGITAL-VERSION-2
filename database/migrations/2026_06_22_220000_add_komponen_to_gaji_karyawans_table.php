<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            // Periode gaji (bulan & tahun yang digaji) -- beda dari tanggal pembayaran
            $table->unsignedTinyInteger('periode_bulan')->nullable()->after('tanggal_transaksi');
            $table->unsignedSmallInteger('periode_tahun')->nullable()->after('periode_bulan');

            // Komponen pendapatan tambahan
            $table->decimal('uang_lembur', 15, 2)->default(0)->after('bonus_lainnya');
            $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan_lainnya');
            $table->decimal('tunjangan_makan', 15, 2)->default(0)->after('tunjangan_transport');

            // Komponen potongan tambahan
            $table->decimal('potongan_bpjs_kesehatan', 15, 2)->default(0)->after('potongan');
            $table->decimal('potongan_bpjs_ketenagakerjaan', 15, 2)->default(0)->after('potongan_bpjs_kesehatan');
            $table->decimal('potongan_pinjaman', 15, 2)->default(0)->after('potongan_bpjs_ketenagakerjaan');
        });
    }

    public function down(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'periode_bulan',
                'periode_tahun',
                'uang_lembur',
                'tunjangan_transport',
                'tunjangan_makan',
                'potongan_bpjs_kesehatan',
                'potongan_bpjs_ketenagakerjaan',
                'potongan_pinjaman',
            ]);
        });
    }
};
