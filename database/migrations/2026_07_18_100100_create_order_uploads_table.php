<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // File yang diunggah customer untuk pesanan JASA (mis. dokumen cek plagiasi).
        Schema::create('order_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->string('path');           // lokasi di storage/app/public
            $table->string('nama_asli');      // nama file asli dari customer
            $table->unsignedBigInteger('ukuran')->nullable(); // byte
            $table->string('mime')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_uploads');
    }
};
