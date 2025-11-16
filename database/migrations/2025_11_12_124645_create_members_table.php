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
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama', 100);
            $table->string('email', 140)->unique();
            $table->string('no_hp', 15)->nullable();
            $table->enum('status_member', ['active', 'non-active'])->default('non-active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama');
            $table->index('status_member');
            $table->index(['nama', 'email'], 'search_name_email_idx');
            $table->index(['status_member', 'created_at'], 'filter_status_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
