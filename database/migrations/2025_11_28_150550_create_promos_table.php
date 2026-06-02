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
        Schema::create('promos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_promo')->unique()->nullable(); // NULL untuk flash sale umum
            $table->string('nama_promo');
            $table->text('deskripsi')->nullable();

            // Tipe Promo
            $table->enum('tipe_promo', ['flash_sale', 'kode_promo', 'referral_bonus'])->default('flash_sale');

            // Tipe Diskon
            $table->enum('tipe_diskon', ['persen', 'nominal'])->default('persen');

            // Nilai Diskon berdasarkan member status
            $table->decimal('diskon_member_persen', 5, 2)->default(0); // Max 999.99%
            $table->decimal('diskon_member_nominal', 15, 0)->default(0);
            $table->decimal('diskon_non_member_persen', 5, 2)->default(0);
            $table->decimal('diskon_non_member_nominal', 15, 0)->default(0);

            // Eligibility
            $table->enum('untuk_member', ['semua', 'member_only', 'non_member_only'])->default('semua');
            $table->boolean('untuk_pembeli_pertama')->default(false);

            // Minimum Purchase
            $table->decimal('min_pembelian', 15, 0)->default(0);

            // Periode Aktif
            $table->timestamp('mulai_promo');
            $table->timestamp('selesai_promo');

            // Status & Priority
            $table->boolean('is_active')->default(true);
            $table->integer('prioritas')->default(0); // Untuk urutan penerapan diskon

            // Stackable Options
            $table->boolean('can_stack_with_other')->default(true); // Bisa digabung dengan promo lain
            $table->boolean('can_stack_with_referral')->default(true);
            $table->boolean('can_stack_with_points')->default(true);

            // Display Options (untuk flash sale)
            $table->boolean('show_on_homepage')->default(false);
            $table->string('banner_image')->nullable();
            $table->string('badge_text')->nullable(); // "FLASH SALE", "SPESIAL"
            $table->string('badge_color')->nullable(); // #FF0000

            // Statistics
            $table->integer('total_penggunaan')->default(0);
            $table->decimal('total_diskon_diberikan', 15, 0)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_promo');
            $table->index('tipe_promo');
            $table->index(['mulai_promo', 'selesai_promo']);
            $table->index(['is_active', 'show_on_homepage']);
        });

        // Pivot table untuk promo-product relationship
        Schema::create('promo_product', function (Blueprint $table) {
            $table->foreignUuid('promo_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promo_id', 'product_id']);
            $table->index('promo_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_product');
        Schema::dropIfExists('promos');
    }
};
