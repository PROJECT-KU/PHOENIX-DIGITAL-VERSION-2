<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Helpdesk perlu jejak tindak lanjut: siapa yang memegang tiket, apakah sudah
 * dibalas, topiknya apa, dan arsip supaya pesan pelanggan (bukti percakapan)
 * tidak hilang permanen sekali klik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->string('kategori', 40)->nullable()->after('priority');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('kategori');
            $table->timestamp('replied_at')->nullable()->after('read_at');
            $table->unsignedBigInteger('replied_by')->nullable()->after('replied_at');
            $table->boolean('is_spam')->default(false)->after('replied_by');
            $table->softDeletes();

            $table->index('kategori');
            $table->index('assigned_to');
            $table->index('is_spam');
        });

        // SQLite (dipakai uji) tidak bisa menambah foreign key lewat ALTER.
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('customer_messages', function (Blueprint $table) {
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                $table->foreign('replied_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('customer_messages', function (Blueprint $table) {
                $table->dropForeign(['assigned_to']);
                $table->dropForeign(['replied_by']);
            });
        }

        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex(['kategori']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['is_spam']);
            $table->dropSoftDeletes();
            $table->dropColumn(['kategori', 'assigned_to', 'replied_at', 'replied_by', 'is_spam']);
        });
    }
};
