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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_akun');
            $table->string('username_akun');
            $table->string('password_akun');
            $table->string('link_login_akun')->nullable();
            $table->string('pj_akun')->nullable(); // Penanggung Jawab
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
