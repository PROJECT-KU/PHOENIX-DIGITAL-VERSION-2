<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->boolean('is_manual')->default(false)->after('status');
            $table->foreignId('dibuat_oleh')->nullable()->after('is_manual')->constrained('users')->nullOnDelete();
        });

        // ===== Permission: presensi manual (input oleh admin) =====
        $now = now();
        DB::table('permissions')->updateOrInsert(
            ['name' => 'create_presensi_manual'],
            [
                'display_name' => 'Presensikan Manual',
                'group' => 'presensi',
                'description' => 'Dapat menginput presensi karyawan secara manual (tanpa batas jarak & waktu)',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $manualId = DB::table('permissions')->where('name', 'create_presensi_manual')->value('id');

        // Berikan ke role yang sudah punya view_all_presensi (admin/HRD).
        $vapId = DB::table('permissions')->where('name', 'view_all_presensi')->value('id');
        $adminRoleIds = $vapId
            ? DB::table('role_permission')->where('permission_id', $vapId)->pluck('role_id')
            : collect();

        foreach ($adminRoleIds as $rid) {
            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $rid, 'permission_id' => $manualId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('role_permission')->whereIn('permission_id', function ($q) {
            $q->select('id')->from('permissions')->where('name', 'create_presensi_manual');
        })->delete();
        DB::table('permissions')->where('name', 'create_presensi_manual')->delete();

        Schema::table('presensis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dibuat_oleh');
            $table->dropColumn('is_manual');
        });
    }
};
