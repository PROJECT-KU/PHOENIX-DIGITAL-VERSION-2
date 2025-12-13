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
        Schema::create('product_bundlings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_paket')->nullable();
            $table->string('product_1')->nullable();
            $table->string('product_2')->nullable();
            $table->string('product_3')->nullable();
            $table->string('product_4')->nullable();
            $table->string('product_5')->nullable();
            $table->string('harga_awal')->nullable();
            $table->string('harga_bundling')->nullable();
            $table->string('gambar')->nullable();
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
        Schema::dropIfExists('product_bundlings');
    }
};
