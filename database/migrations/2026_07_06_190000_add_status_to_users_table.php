<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status akun: 'active' normal, 'blokir' terkunci setelah 3x gagal login.
            $table->enum('status', ['active', 'blokir'])->default('active')->after('role_id');
            // Penghitung gagal login (persisten) untuk memicu blokir.
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'failed_login_attempts']);
        });
    }
};
