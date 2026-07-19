<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah 'sekali' ke enum durasi_type untuk produk JASA (sekali bayar,
        // tanpa durasi). Nilai lama 'bulan'/'tahun' tidak terpengaruh.
        DB::statement("ALTER TABLE product_prices MODIFY durasi_type ENUM('bulan','tahun','sekali') NOT NULL");
    }

    public function down(): void
    {
        // Kembalikan ke enum semula; baris 'sekali' (jika ada) dijadikan 'bulan'
        // agar tak melanggar enum.
        DB::table('product_prices')->where('durasi_type', 'sekali')->update(['durasi_type' => 'bulan']);
        DB::statement("ALTER TABLE product_prices MODIFY durasi_type ENUM('bulan','tahun') NOT NULL");
    }
};
