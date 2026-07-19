<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jejak "sudah dibaca" komentar task PER user. Kolom lama karyawan_read_at
     * hanya cukup untuk 2 pihak (admin vs karyawan); dengan hierarki (penerima,
     * pemberi, atasan) tiap orang butuh status baca sendiri untuk badge kartu.
     */
    public function up(): void
    {
        Schema::create('task_comment_reads', function (Blueprint $table) {
            $table->id();
            $table->uuid('task_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comment_reads');
    }
};
