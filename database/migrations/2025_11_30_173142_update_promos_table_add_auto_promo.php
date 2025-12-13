<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            DB::statement("ALTER TABLE promos MODIFY COLUMN tipe_promo ENUM('flash_sale', 'kode_promo', 'referral_bonus', 'auto_promo') DEFAULT 'flash_sale'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            DB::statement("ALTER TABLE promos MODIFY COLUMN tipe_promo ENUM('flash_sale', 'kode_promo', 'referral_bonus') DEFAULT 'flash_sale'");
        });
    }
};
