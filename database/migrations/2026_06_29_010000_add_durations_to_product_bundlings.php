<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_bundlings', function (Blueprint $table) {
            // Durasi tiap produk dalam paket, mis. ['product_1' => ['value'=>1,'type'=>'tahun'], ...]
            $table->json('durations')->nullable()->after('product_5');
        });
    }

    public function down(): void
    {
        Schema::table('product_bundlings', function (Blueprint $table) {
            $table->dropColumn('durations');
        });
    }
};
