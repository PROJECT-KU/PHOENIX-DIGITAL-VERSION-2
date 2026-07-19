<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->unsignedSmallInteger('durasi_value');
            $table->enum('durasi_type', ['bulan', 'tahun']);
            $table->decimal('harga', 15, 0)->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['product_id', 'durasi_value', 'durasi_type']);
        });

        // Migrasi harga lama (kolom tetap) -> baris fleksibel
        $now = now();
        $rows = [];
        DB::table('products')->orderBy('id')->each(function ($p) use (&$rows, $now) {
            $map = [
                ['v' => 1, 't' => 'bulan', 'h' => $p->harga_perbulan],
                ['v' => 5, 't' => 'bulan', 'h' => $p->harga_5_perbulan],
                ['v' => 10, 't' => 'bulan', 'h' => $p->harga_10_perbulan],
                ['v' => 1, 't' => 'tahun', 'h' => $p->harga_pertahun],
            ];
            foreach ($map as $m) {
                if ($m['h'] !== null && (int) $m['h'] > 0) {
                    DB::table('product_prices')->insert([
                        'id' => (string) Str::uuid(),
                        'product_id' => $p->id,
                        'durasi_value' => $m['v'],
                        'durasi_type' => $m['t'],
                        'harga' => (int) $m['h'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
