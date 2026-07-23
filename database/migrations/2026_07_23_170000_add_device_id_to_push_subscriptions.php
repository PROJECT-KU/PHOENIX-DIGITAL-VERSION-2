<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * device_id: identitas stabil per perangkat/instal (UUID di localStorage klien).
 * Dipakai agar 1 PERANGKAT = 1 langganan: saat endpoint push ter-rotasi (iOS/FCM
 * kadang mengganti endpoint), server meng-update baris device yang sama alih-alih
 * membuat baris baru → tak ada notifikasi dobel. Nullable untuk kompatibel dgn
 * baris lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('device_id', 64)->nullable()->after('user_id');
            $table->index(['user_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'device_id']);
            $table->dropColumn('device_id');
        });
    }
};
