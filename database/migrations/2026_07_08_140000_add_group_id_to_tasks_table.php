<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grouping task multi-penerima. Satu "task" yang di-assign ke banyak orang
     * dipecah jadi beberapa baris sub-task (satu per penerima) yang berbagi
     * group_id sama. Progres/bonus tetap per baris; komentar di level group_id.
     *
     * Backfill: setiap task lama jadi grup beranggota satu (group_id = id-nya).
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('group_id')->nullable()->after('id')->index();
        });

        DB::statement('UPDATE tasks SET group_id = id WHERE group_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
