<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Linimasa satu tiket: dibaca, status berubah, ditugaskan, dibalas, catatan
 * internal. Satu tabel untuk semuanya supaya halaman detail bisa menampilkan
 * kronologi apa adanya, dan "Selesai" punya bukti — bukan sekadar klaim.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_message_id')->constrained()->cascadeOnDelete();
            // Pelakunya boleh hilang (karyawan keluar) tanpa menghapus jejaknya.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nama_pelaku')->nullable();
            $table->string('jenis', 20);
            $table->string('kanal', 20)->nullable();
            $table->text('isi')->nullable();
            $table->timestamps();

            $table->index(['customer_message_id', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_message_logs');
    }
};
