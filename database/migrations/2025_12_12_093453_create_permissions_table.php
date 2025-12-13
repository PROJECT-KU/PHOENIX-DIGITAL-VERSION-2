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
        // Tabel permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // misal: view_pemesanantoko, create_pemesanantoko
            $table->string('display_name'); // nama yang ditampilkan: "Lihat Pemesanan Toko"
            $table->string('group')->nullable(); // untuk grouping: "pemesanantoko", "users", dll
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot table: role memiliki banyak permissions
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Pastikan tidak ada duplikat
            $table->unique(['role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
    }
};
