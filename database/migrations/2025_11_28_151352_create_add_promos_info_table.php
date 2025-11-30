<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('applied_promos')->nullable()->after('points_discount'); // Array promo yang digunakan
            $table->decimal('promo_discount', 15, 0)->default(0)->after('points_discount');
            $table->decimal('referral_discount', 15, 0)->default(0)->after('promo_discount');
            $table->decimal('total_discount', 15, 0)->default(0)->after('referral_discount'); // Total semua diskon

        });

        // Table untuk tracking penggunaan promo per order
        Schema::create('order_promo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('promo_id')->constrained()->onDelete('cascade');

            $table->string('kode_promo')->nullable();
            $table->enum('tipe_diskon', ['persen', 'nominal']);
            $table->decimal('nilai_diskon', 15, 2); // Nilai persen atau nominal yang digunakan
            $table->decimal('jumlah_diskon', 15, 0); // Hasil diskon dalam rupiah

            $table->timestamps();

            $table->index('order_id');
            $table->index('promo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_promo');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'applied_promos',
                'promo_discount',
                'referral_discount',
                'total_discount',
            ]);
        });
    }
};
