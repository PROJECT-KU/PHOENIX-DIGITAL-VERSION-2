<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // 'error' (exception/HTTP 500) atau 'auth' (login/logout/gagal login).
            $table->string('type')->index();
            // 'error' | 'warning' | 'info' — untuk pewarnaan & filter cepat.
            $table->string('level')->default('info')->index();
            // 'exception' | 'login' | 'logout' | 'login_failed'
            $table->string('event')->nullable();

            $table->text('message');

            // Konteks ERROR
            $table->string('exception_class')->nullable();
            $table->text('file')->nullable();
            $table->integer('line')->nullable();
            $table->integer('status_code')->nullable();
            $table->longText('trace')->nullable();

            // Konteks REQUEST (public maupun admin)
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();

            // Siapa (tanpa FK agar tak mengikat tabel users; user bisa null).
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
