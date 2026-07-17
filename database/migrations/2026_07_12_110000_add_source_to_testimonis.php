<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            // 'admin' = dibuat admin, 'customer' = dikirim langsung oleh pelanggan
            $table->string('source')->default('admin')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
