<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'view_testimoni', 'display_name' => 'Lihat Testimoni', 'src' => 'view_banners'],
            ['name' => 'create_testimoni', 'display_name' => 'Buat Testimoni', 'src' => 'create_banners'],
            ['name' => 'edit_testimoni', 'display_name' => 'Edit Testimoni', 'src' => 'edit_banners'],
            ['name' => 'delete_testimoni', 'display_name' => 'Hapus Testimoni', 'src' => 'delete_banners'],
        ];

        foreach ($permissions as $p) {
            $permId = DB::table('permissions')->where('name', $p['name'])->value('id');
            if (! $permId) {
                $permId = DB::table('permissions')->insertGetId([
                    'name' => $p['name'],
                    'display_name' => $p['display_name'],
                    'group' => 'testimoni',
                    'description' => $p['display_name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Berikan ke setiap role yang sudah punya permission banner padanannya
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
            ->whereIn('name', ['view_testimoni', 'create_testimoni', 'edit_testimoni', 'delete_testimoni'])
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
