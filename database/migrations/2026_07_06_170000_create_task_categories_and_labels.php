<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('task_category_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_category_id')->constrained('task_categories')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_category_id')->nullable()->after('deskripsi')
                ->constrained('task_categories')->nullOnDelete();
            $table->foreignId('task_category_label_id')->nullable()->after('task_category_id')
                ->constrained('task_category_labels')->nullOnDelete();
        });

        // Seed kategori default + label khas programming.
        $now = now();
        $defaults = ['Programming', 'Surat Menyurat', 'Desain', 'Laporan Keuangan'];
        foreach ($defaults as $nama) {
            $id = DB::table('task_categories')->insertGetId([
                'nama' => $nama, 'created_at' => $now, 'updated_at' => $now,
            ]);
            if ($nama === 'Programming') {
                foreach (['Bug', 'Improvement', 'Feature', 'Refactor'] as $lab) {
                    DB::table('task_category_labels')->insert([
                        'task_category_id' => $id, 'nama' => $lab,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_category_id');
            $table->dropConstrainedForeignId('task_category_label_id');
        });
        Schema::dropIfExists('task_category_labels');
        Schema::dropIfExists('task_categories');
    }
};
