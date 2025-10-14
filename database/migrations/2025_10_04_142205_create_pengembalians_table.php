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
        Schema::create('pengembalians', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            $table->uuid('id')->primary();
            $table->string('nama_pengembalian');             // nama pengembalian
            $table->date('tanggal_pengembalian');            // tanggal pinjam
            $table->decimal('nominal', 15, 2);           // jumlah nominal pinjaman
            $table->text('deskripsi')->nullable();       // deskripsi
            $table->enum('status', ['pending', 'lunas', 'berjalan'])->default('pending'); // status pinjaman
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // siapa yang input (auth user)
            $table->timestamps();

            // Indexes untuk performa
            $table->index('tanggal_pengembalian');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalians');
    }
};
