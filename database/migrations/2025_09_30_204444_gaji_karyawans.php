<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gaji_karyawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('id_transaksi');
            $table->foreignId('nama_karyawan')->nullable()->constrained('users')->onDelete('cascade'); // karyawan pembeli
            $table->string('bank')->nullable();
            $table->string('no_rek')->nullable();
            $table->date('tanggal_transaksi');
            $table->string('gaji_pokok')->nullable();
            $table->string('bonus_kinerja')->nullable();
            $table->string('bonus_lainnya')->nullable();
            $table->string('tunjangan_kesehatan')->nullable();
            $table->string('tunjangan_thr')->nullable();
            $table->string('tunjangan_ketenagakerjaan')->nullable();
            $table->string('tunjangan_lainnya')->nullable();
            $table->string('potongan')->nullable();
            $table->string('pph21')->nullable();
            $table->string('total')->nullable();
            $table->text('deskripsi');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();

            // Indexes untuk performa
            $table->index('tanggal_transaksi');
            $table->index('status');
            $table->index('nama_karyawan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji_karyawans');
    }
};
