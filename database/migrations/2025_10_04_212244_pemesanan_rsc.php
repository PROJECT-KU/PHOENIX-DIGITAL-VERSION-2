<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_rsc', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Data camp
            $table->string('id_transaksi');
            $table->string('nama_camp');
            $table->string('batch_camp');
            $table->date('tanggal_mulai_camp');
            $table->date('tanggal_akhir_camp');

            // Data pembeli
            $table->string('nama_pembeli')->nullable();
            $table->string('telp_pembeli')->nullable();
            $table->string('jumlah_pemesanan')->nullable();
            $table->date('tanggal_pemesanan');
            $table->date('tanggal_berakhir');

            // Foreign ke products (uuid)
            $table->uuid('akun')->nullable()->index();

            // Data produk
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('link_akses')->nullable();
            $table->string('harga_satuan')->nullable();
            $table->string('total')->nullable();

            // Foreign ke users
            $table->foreignId('pic')->constrained('users')->onDelete('cascade');

            $table->text('deskripsi')->nullable();
            $table->enum('status', ['habis', 'pengganti', 'perpanjang', 'baru'])->default('baru');
            $table->timestamps();

            // Index yang valid
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_rsc');
    }
};
