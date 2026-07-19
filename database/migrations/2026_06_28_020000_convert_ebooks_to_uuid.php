<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ebook & pivot masih baru — buat ulang memakai UUID (untuk akses publik).
        Schema::dropIfExists('order_item_ebook');
        Schema::dropIfExists('ebooks');

        Schema::create('ebooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file');
            $table->enum('status', ['active', 'non-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_item_ebook', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignUuid('ebook_id')->constrained('ebooks')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['order_item_id', 'ebook_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_ebook');
        Schema::dropIfExists('ebooks');

        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file');
            $table->enum('status', ['active', 'non-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_item_ebook', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('ebook_id')->constrained('ebooks')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['order_item_id', 'ebook_id']);
        });
    }
};
