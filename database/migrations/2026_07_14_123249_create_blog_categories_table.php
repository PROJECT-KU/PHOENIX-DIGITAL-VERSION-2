<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Isi otomatis dari kategori yang sudah dipakai artikel (agar tidak hilang).
        if (Schema::hasTable('blog_posts')) {
            $existing = DB::table('blog_posts')
                ->whereNotNull('category')->where('category', '!=', '')
                ->distinct()->pluck('category');

            foreach ($existing as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                DB::table('blog_categories')->insertOrIgnore([
                    'name' => $name,
                    'slug' => Str::slug($name) ?: Str::random(8),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};
