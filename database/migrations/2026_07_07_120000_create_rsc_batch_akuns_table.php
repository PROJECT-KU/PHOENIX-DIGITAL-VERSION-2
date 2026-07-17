<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Akun tambahan per batch (kredensial saja, tidak memengaruhi harga/hitungan).
        Schema::create('rsc_batch_akuns', function (Blueprint $table) {
            $table->id();
            $table->string('nama_camp');
            $table->string('batch_camp');
            $table->unsignedBigInteger('akun_id')->nullable();
            $table->string('nama_akun')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('link_akses')->nullable();
            $table->timestamps();

            $table->index(['nama_camp', 'batch_camp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsc_batch_akuns');
    }
};
