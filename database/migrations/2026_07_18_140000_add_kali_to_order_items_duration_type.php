<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * order_items.duration_type semula enum('bulan','tahun'). Produk JASA memakai
     * 'kali' (jumlah pengecekan). Tanpa nilai ini, checkout jasa GAGAL diam-diam
     * ("Data truncated"). Tambah 'sekali'+'kali' — additif, tak ubah data lama.
     */
    public function up(): void
    {
        // ALTER ... MODIFY hanya dikenal MySQL. Uji otomatis memakai SQLite
        // (phpunit.xml), yang tidak memaksakan ENUM sama sekali, jadi di sana
        // baris ini memang tidak perlu dijalankan. Perilaku MySQL tak berubah.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM('bulan','tahun','sekali','kali') NOT NULL DEFAULT 'bulan'");
        }
    }

    public function down(): void
    {
        // ALTER ... MODIFY hanya dikenal MySQL. Uji otomatis memakai SQLite
        // (phpunit.xml), yang tidak memaksakan ENUM sama sekali, jadi di sana
        // baris ini memang tidak perlu dijalankan. Perilaku MySQL tak berubah.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM('bulan','tahun') NOT NULL DEFAULT 'bulan'");
        }
    }
};
