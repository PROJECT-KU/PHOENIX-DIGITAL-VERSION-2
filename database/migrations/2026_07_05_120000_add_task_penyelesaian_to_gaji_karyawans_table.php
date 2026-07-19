<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            // Budget pool untuk penyelesaian task pada periode ini
            $table->unsignedBigInteger('task_budget')->default(0)->after('bonus_lainnya');
            // Hasil bonus penyelesaian task (budget x bobot x status%) — komponen pendapatan
            $table->unsignedBigInteger('bonus_penyelesaian_task')->default(0)->after('task_budget');
            // Daftar task: [{nama, bobot: ringan|sedang|berat, status: tepat_waktu|terlambat|tidak_selesai|tidak_ada_info}]
            $table->json('tasks')->nullable()->after('bonus_penyelesaian_task');
        });
    }

    public function down(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->dropColumn(['task_budget', 'bonus_penyelesaian_task', 'tasks']);
        });
    }
};
