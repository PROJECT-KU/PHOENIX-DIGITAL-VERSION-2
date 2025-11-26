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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama', 100);
            $table->string('email', 140)->unique();
            $table->string('no_hp', 15)->nullable();
            $table->string('kode_ref')->nullable();
            $table->enum('status_member', ['active', 'non-active'])->default('non-active');
            $table->timestamp('member_since')->nullable();
            $table->decimal('point', 15, 0)->default(0);
            $table->decimal('point_balance', 15, 0)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama');
            $table->index('no_hp');
            $table->index('email');
            $table->index('status_member');
            $table->index(['nama', 'email', 'no_hp'], 'search_name_email_no_hp_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
