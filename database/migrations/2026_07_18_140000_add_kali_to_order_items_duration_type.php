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
        DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM('bulan','tahun','sekali','kali') NOT NULL DEFAULT 'bulan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM('bulan','tahun') NOT NULL DEFAULT 'bulan'");
    }
};
