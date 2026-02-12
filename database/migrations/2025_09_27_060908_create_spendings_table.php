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
        Schema::create('spendings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('id_transaksi')->unique();
            $table->date('tanggal_transaksi');
            $table->decimal('nominal', 15, 0);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->enum('jenis_pengeluaran', ['pembelian_akun', 'lainnya'])->default('pembelian_akun');
            $table->foreignId('penginput_id')->constrained('users')->onDelete('cascade'); // user yang sedang login
            $table->foreignId('pic_pembeli_id')->nullable()->constrained('users')->onDelete('cascade'); // karyawan pembeli
            $table->timestamps();

            // Indexes untuk performa
            $table->index('tanggal_transaksi');
            $table->index('status');
            $table->index('penginput_id');
            $table->index('pic_pembeli_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spendings');
    }
};
