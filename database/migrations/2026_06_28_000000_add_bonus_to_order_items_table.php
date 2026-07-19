<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Bonus durasi tambahan (mis. beli 1 bulan + bonus 2 bulan)
            $table->integer('bonus_duration_value')->nullable()->after('duration_value');
            $table->enum('bonus_duration_type', ['bulan', 'tahun'])->nullable()->after('bonus_duration_value');

            // Bonus barang/ebook (deskripsi + file yang diupload admin)
            $table->string('bonus_description')->nullable()->after('account_notes');
            $table->string('bonus_file')->nullable()->after('bonus_description');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'bonus_duration_value',
                'bonus_duration_type',
                'bonus_description',
                'bonus_file',
            ]);
        });
    }
};
