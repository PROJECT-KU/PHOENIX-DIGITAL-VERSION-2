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
        Schema::create('data_akuns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_akun');
            $table->string('username_akun');
            $table->string('password_akun');
            $table->string('link_login_akun')->nullable();
            $table->string('pj_akun')->nullable();
            $table->string('harga_satuan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['active', 'non-active'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_akuns');
    }
};
