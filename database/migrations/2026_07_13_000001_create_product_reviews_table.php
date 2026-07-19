<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('product_id');
            $table->string('nama', 60);
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->text('ulasan');
            $table->string('status')->default('approved'); // approved | pending | hidden
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
