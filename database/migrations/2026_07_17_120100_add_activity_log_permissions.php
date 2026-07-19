<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'view_activity_log', 'display_name' => 'Lihat Log Aktivitas', 'src' => 'view_permission'],
            ['name' => 'clear_activity_log', 'display_name' => 'Bersihkan Log Aktivitas', 'src' => 'delete_permission'],
        ];

        foreach ($permissions as $p) {
            $permId = DB::table('permissions')->where('name', $p['name'])->value('id');
            if (! $permId) {
                $permId = DB::table('permissions')->insertGetId([
                    'name' => $p['name'],
                    'display_name' => $p['display_name'],
                    'group' => 'activity_log',
                    'description' => $p['display_name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Berikan ke setiap role yang sudah punya permission padanannya.
            $srcId = DB::table('permissions')->where('name', $p['src'])->value('id');
            if ($srcId) {
                $roleIds = DB::table('role_permission')->where('permission_id', $srcId)->pluck('role_id');
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
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', ['view_activity_log', 'clear_activity_log'])
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
