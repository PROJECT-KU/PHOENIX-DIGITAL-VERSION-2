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
        Schema::create('loans', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();
            $table->string('id_transaksi')->unique();
            $table->string('nama_peminjam');             // nama peminjam
            $table->date('tanggal_peminjam');            // tanggal pinjam
            $table->decimal('nominal', 15, 2);           // jumlah nominal pinjaman
            $table->text('deskripsi')->nullable();       // deskripsi
            $table->enum('status', ['pending', 'lunas', 'berjalan'])->default('pending'); // status pinjaman
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // siapa yang input (auth user)
            $table->timestamps();

            // Indexes untuk performa
            $table->index('tanggal_peminjam');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
