<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Atasan langsung karyawan (relasi hierarki). Dipakai fitur "Task Saya":
     * karyawan yang punya bawahan boleh memberi task ke seluruh downline-nya.
     */
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->foreignId('atasan_id')->nullable()->after('jabatan')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('atasan_id');
        });
    }
};
