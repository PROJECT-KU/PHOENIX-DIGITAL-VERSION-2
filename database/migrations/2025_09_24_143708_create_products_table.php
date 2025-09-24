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
            $table->id();
            $table->string('nama_akun');
            $table->string('image')->nullable();
            $table->decimal('harga_perbulan', 10, 2)->nullable();
            $table->decimal('harga_5_perbulan', 10, 2)->nullable();
            $table->decimal('harga_10_perbulan', 10, 2)->nullable();
            $table->decimal('harga_pertahun', 10, 2)->nullable();
            $table->text('deskripsi')->nullable();
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
