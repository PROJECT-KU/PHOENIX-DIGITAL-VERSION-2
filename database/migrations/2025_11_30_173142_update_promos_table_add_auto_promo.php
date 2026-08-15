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
            // ALTER ... MODIFY hanya dikenal MySQL. Uji otomatis memakai SQLite
            // (phpunit.xml), yang tidak memaksakan ENUM sama sekali, jadi di sana
            // baris ini memang tidak perlu dijalankan. Perilaku MySQL tak berubah.
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE promos MODIFY COLUMN tipe_promo ENUM('flash_sale', 'kode_promo', 'referral_bonus', 'auto_promo') DEFAULT 'flash_sale'");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            // ALTER ... MODIFY hanya dikenal MySQL. Uji otomatis memakai SQLite
            // (phpunit.xml), yang tidak memaksakan ENUM sama sekali, jadi di sana
            // baris ini memang tidak perlu dijalankan. Perilaku MySQL tak berubah.
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE promos MODIFY COLUMN tipe_promo ENUM('flash_sale', 'kode_promo', 'referral_bonus') DEFAULT 'flash_sale'");
            }
        });
    }
};
