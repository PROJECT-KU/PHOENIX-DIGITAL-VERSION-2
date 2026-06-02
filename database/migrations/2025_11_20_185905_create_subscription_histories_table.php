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
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');

            // Previous subscription info (jika perpanjangan)
            $table->uuid('previous_order_item_id')->nullable();
            $table->date('previous_end_date')->nullable();

            // Current subscription
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months');

            $table->enum('action_type', ['baru', 'perpanjang', 'pengganti']);
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
    }
};
