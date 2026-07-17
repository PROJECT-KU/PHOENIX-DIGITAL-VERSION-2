<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Komentar pindah ke level GROUP (bukan per sub-task) + bisa di-pin.
     * task_id tetap disimpan sbg referensi sub-task tempat komentar ditulis,
     * tapi dibuat nullOnDelete agar menghapus satu sub-task (penerima) TIDAK
     * ikut menghapus komentar grup.
     */
    public function up(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            $table->uuid('group_id')->nullable()->after('task_id')->index();
            $table->timestamp('pinned_at')->nullable()->after('file_name');
        });

        // Isi group_id dari group_id task terkait.
        DB::statement('UPDATE task_comments tc JOIN tasks t ON t.id = tc.task_id SET tc.group_id = t.group_id WHERE tc.group_id IS NULL');

        // Ganti perilaku FK task_id: cascade -> nullOnDelete (komentar grup bertahan).
        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->uuid('task_id')->nullable()->change();
            $table->foreign('task_id')->references('id')->on('tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->dropIndex(['group_id']);
            $table->dropColumn(['group_id', 'pinned_at']);
        });
    }
};
