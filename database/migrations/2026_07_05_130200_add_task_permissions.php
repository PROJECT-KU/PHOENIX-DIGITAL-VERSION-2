<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $ensurePerm = function (string $name, string $display) use ($now): int {
            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'display_name' => $display,
                    'group' => 'task',
                    'description' => $display,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $id;
        };

        $grant = function (int $permId, iterable $roleIds) use ($now) {
            foreach ($roleIds as $roleId) {
                $exists = DB::table('role_permission')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permId)
                    ->exists();
                if (! $exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        };

        // view_task -> semua role (setiap karyawan bisa lihat task miliknya)
        $viewTask = $ensurePerm('view_task', 'Lihat Task Saya');
        $grant($viewTask, DB::table('roles')->pluck('id'));

        // manage_task & view_all_task -> role yang punya create_gajikaryawan (admin)
        $adminRoleIds = DB::table('role_permission')
            ->where('permission_id', DB::table('permissions')->where('name', 'create_gajikaryawan')->value('id'))
            ->pluck('role_id');

        $grant($ensurePerm('manage_task', 'Kelola Task (Admin)'), $adminRoleIds);
        $grant($ensurePerm('view_all_task', 'Lihat Semua Task'), $adminRoleIds);
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', ['view_task', 'manage_task', 'view_all_task'])
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
