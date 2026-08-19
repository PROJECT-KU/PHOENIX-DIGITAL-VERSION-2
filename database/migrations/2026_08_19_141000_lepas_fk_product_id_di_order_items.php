<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `order_items.product_id` kini sah berisi id Product ATAU id ProductBundlings,
 * karena paket bundling dijual sebagai satu baris pesanan. Foreign key ke
 * tabel `products` menolak id paket, sehingga checkout paket gagal menyimpan
 * baris itemnya dan pembeli tidak pernah sampai ke halaman QRIS.
 *
 * Constraint dilepas, bukan diganti relasi polimorfik, agar data pesanan yang
 * sudah ada tidak perlu dipindahkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL: lepas hanya bila constraint-nya memang ada. Sebagian basis data
        // hasil impor tidak membawanya, dan dropForeign() akan menggagalkan
        // migrasi bila dipaksa.
        if (DB::getDriverName() === 'mysql') {
            $ada = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                   AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                ['order_items', 'product_id']
            );

            foreach ($ada as $fk) {
                DB::statement('ALTER TABLE `order_items` DROP FOREIGN KEY `'.$fk->CONSTRAINT_NAME.'`');
            }

            return;
        }

        // SQLite (dipakai pengujian): tabel ditulis ulang oleh Laravel.
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }

    public function down(): void
    {
        // Sengaja tidak dipasang kembali: mengembalikannya akan menolak baris
        // pesanan paket yang sudah tersimpan.
    }
};
