<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->enum('bobot', ['ringan', 'sedang', 'berat'])->default('sedang');
            $table->date('deadline_mulai');
            $table->date('deadline_selesai');
            $table->enum('progress', ['belum', 'dikerjakan', 'selesai'])->default('belum');
            $table->timestamp('completed_at')->nullable();
            // Dedup notifikasi
            $table->timestamp('assigned_notified_at')->nullable();
            $table->timestamp('deadline_notified_at')->nullable();
            $table->timestamp('overdue_notified_at')->nullable();
            $table->timestamps();

            $table->index(['periode_bulan', 'periode_tahun']);
            $table->index(['user_id', 'periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
