<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tunda tiket ("tunggu jawaban pelanggan sampai Senin").
 *
 * Tanpa ini satu-satunya cara menandainya adalah catatan internal, sementara
 * tiketnya tetap menghitung mundur seolah diabaikan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->timestamp('tunda_sampai')->nullable()->after('merged_into');
            $table->index('tunda_sampai');
        });
    }

    public function down(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex(['tunda_sampai']);
            $table->dropColumn('tunda_sampai');
        });
    }
};
