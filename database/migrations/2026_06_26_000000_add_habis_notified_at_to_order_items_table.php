<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Penanda kapan notifikasi "akun habis" dikirim ke pelanggan via WhatsApp
            $table->timestamp('habis_notified_at')->nullable()->after('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('habis_notified_at');
        });
    }
};
