<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spendings', function (Blueprint $table) {
            // Untuk pengeluaran "Pembelian Akun": produk yang dibeli (dasar modal per produk).
            $table->uuid('product_id')->nullable()->after('jenis_pengeluaran');
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('spendings', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
