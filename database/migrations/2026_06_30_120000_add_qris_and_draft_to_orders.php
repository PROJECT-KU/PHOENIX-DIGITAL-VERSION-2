<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah status 'draft' ke enum status order
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','paid','processing','completed','cancelled','draft') NOT NULL DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            // Payload QRIS dinamis (EMV string) untuk dirender jadi QR
            $table->text('qris_content')->nullable()->after('payment_url');
            // Nomor transaksi unik yang dikirim ke provider (regenerate = nomor baru)
            $table->string('qris_trx_id')->nullable()->after('qris_content');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['qris_content', 'qris_trx_id']);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','paid','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
