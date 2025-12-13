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
            $table->string('id_transaksi')->unique();
            $table->string('nama_pengembalian');
            $table->date('tanggal_pengembalian');
            $table->decimal('nominal', 15, 2);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['pending', 'lunas', 'berjalan'])->default('pending');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
