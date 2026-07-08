<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DataAkun memakai primary key UUID, jadi akun_id harus string.
        Schema::table('rsc_batch_akuns', function (Blueprint $table) {
            $table->string('akun_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rsc_batch_akuns', function (Blueprint $table) {
            $table->unsignedBigInteger('akun_id')->nullable()->change();
        });
    }
};
