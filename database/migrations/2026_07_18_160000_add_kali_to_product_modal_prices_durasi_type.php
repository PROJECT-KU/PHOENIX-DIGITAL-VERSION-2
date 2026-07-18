<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * product_modal_prices.durasi_type semula enum('bulan','tahun'). Modal produk
     * JASA dicatat per '1 kali' pengecekan, jadi butuh nilai 'kali' (+ 'sekali'
     * untuk keselarasan dengan tabel lain). Additif — tak mengubah data lama.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE product_modal_prices MODIFY durasi_type ENUM('bulan','tahun','sekali','kali') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE product_modal_prices MODIFY durasi_type ENUM('bulan','tahun') NOT NULL");
    }
};
