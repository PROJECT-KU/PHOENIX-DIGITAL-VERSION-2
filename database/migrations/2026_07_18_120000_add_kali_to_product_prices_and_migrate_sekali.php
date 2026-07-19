<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah 'kali' (paket jasa per JUMLAH PENGECEKAN, mis. 1x, 5x).
        DB::statement("ALTER TABLE product_prices MODIFY durasi_type ENUM('bulan','tahun','sekali','kali') NOT NULL");

        // Selaraskan produk jasa lama: 'sekali' -> 'kali' (durasi_value = jumlah).
        DB::table('product_prices')->where('durasi_type', 'sekali')->update(['durasi_type' => 'kali']);
    }

    public function down(): void
    {
        DB::table('product_prices')->where('durasi_type', 'kali')->update(['durasi_type' => 'sekali']);
        DB::statement("ALTER TABLE product_prices MODIFY durasi_type ENUM('bulan','tahun','sekali') NOT NULL");
    }
};
