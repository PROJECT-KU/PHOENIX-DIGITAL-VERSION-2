<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            // Kapan komentar sudah dibaca oleh pihak lawan
            $table->timestamp('admin_read_at')->nullable()->after('file_name');
            $table->timestamp('karyawan_read_at')->nullable()->after('admin_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropColumn(['admin_read_at', 'karyawan_read_at']);
        });
    }
};
