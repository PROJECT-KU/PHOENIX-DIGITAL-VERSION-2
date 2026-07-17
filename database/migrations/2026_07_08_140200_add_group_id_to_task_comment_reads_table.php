<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status baca komentar kini per GROUP (bukan per sub-task), sebab komentar
     * dibagikan di level group. Idempoten (aman diulang bila sebagian sudah jalan).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('task_comment_reads', 'group_id')) {
            Schema::table('task_comment_reads', function (Blueprint $table) {
                $table->uuid('group_id')->nullable()->after('task_id')->index();
            });
        }

        DB::statement('UPDATE task_comment_reads tcr JOIN tasks t ON t.id = tcr.task_id SET tcr.group_id = t.group_id WHERE tcr.group_id IS NULL');

        // FK task_id memakai index unik komposit; lepas dulu agar unik bisa diganti.
        // Reads kini di-key oleh group_id; task_id cukup jadi referensi tanpa FK.
        if ($this->foreignKeyExists('task_comment_reads', 'task_comment_reads_task_id_foreign')) {
            Schema::table('task_comment_reads', function (Blueprint $table) {
                $table->dropForeign(['task_id']);
            });
        }
        if ($this->indexExists('task_comment_reads', 'task_comment_reads_task_id_user_id_unique')) {
            Schema::table('task_comment_reads', function (Blueprint $table) {
                $table->dropUnique(['task_id', 'user_id']);
            });
        }
        if (! $this->indexExists('task_comment_reads', 'task_comment_reads_group_id_user_id_unique')) {
            Schema::table('task_comment_reads', function (Blueprint $table) {
                $table->unique(['group_id', 'user_id']);
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return count(DB::select(
            'SELECT 1 FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = "FOREIGN KEY" LIMIT 1',
            [$table, $constraint]
        )) > 0;
    }

    public function down(): void
    {
        Schema::table('task_comment_reads', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'user_id']);
            $table->unique(['task_id', 'user_id']);
            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return count(DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $index]
        )) > 0;
    }
};
