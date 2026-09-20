<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lampiran tiket & penggabungan tiket ganda.
 *
 * Komplain hampir selalu butuh tangkapan layar, dan satu orang sering mengirim
 * masalah yang sama dua kali lewat formulir kontak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_message_id')->constrained()->cascadeOnDelete();
            // pelanggan = ikut formulir kontak, admin = ditambahkan dari panel.
            $table->string('sumber', 12)->default('pelanggan');
            $table->string('nama_asli');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('ukuran')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('customer_message_id', 'cma_pesan_idx');
        });

        Schema::table('customer_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('merged_into')->nullable()->after('is_spam');
            $table->index('merged_into');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('customer_message_attachments', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex(['merged_into']);
            $table->dropColumn('merged_into');
        });

        Schema::dropIfExists('customer_message_attachments');
    }
};
