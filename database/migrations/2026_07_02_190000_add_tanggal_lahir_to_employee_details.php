<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->date('tanggal_lahir')->nullable()->after('nomor_rekening');
        });

        // Bank dikunci ke Bank Mandiri untuk semua karyawan yang sudah punya rekening.
        DB::table('employee_details')->whereNotNull('nomor_rekening')->update(['nama_bank' => 'Bank Mandiri']);
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('tanggal_lahir');
        });
    }
};
