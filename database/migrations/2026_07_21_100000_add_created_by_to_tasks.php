<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catat SIAPA yang membuat task, terpisah dari assigned_by.
 *
 * Task yang dibuat admin lewat "Penyelesaian Task" sengaja punya assigned_by
 * NULL — konvensi itu memisahkan task admin dari rantai kelola bawahan
 * (manageableGiverIds) dan TIDAK boleh diubah. Akibatnya nama pembuat tidak
 * pernah tersimpan, sehingga di "Task Saya" pemberinya tampil sebagai "Admin".
 *
 * Kolom created_by ini murni untuk menampilkan nama pembuat, tanpa menyentuh
 * makna assigned_by.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('assigned_by');
        });

        // Backfill: task lama tanpa assigned_by adalah task admin. Bila di sistem
        // ini hanya ada SATU administrator, aman menetapkan dia sebagai pembuat.
        // Jika lebih dari satu, dibiarkan null (tampilan tetap jatuh ke "Admin").
        $adminIds = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.name', 'administrator')
            ->pluck('users.id');

        if ($adminIds->count() === 1) {
            DB::table('tasks')
                ->whereNull('assigned_by')
                ->whereNull('created_by')
                ->update(['created_by' => $adminIds->first()]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
