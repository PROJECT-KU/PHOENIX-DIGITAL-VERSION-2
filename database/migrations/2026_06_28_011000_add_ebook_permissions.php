<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'view_ebook', 'display_name' => 'Lihat Ebook Bonus', 'src' => 'view_pemesanantoko'],
            ['name' => 'create_ebook', 'display_name' => 'Buat Ebook Bonus', 'src' => 'create_pemesanantoko'],
            ['name' => 'edit_ebook', 'display_name' => 'Edit Ebook Bonus', 'src' => 'edit_pemesanantoko'],
            ['name' => 'delete_ebook', 'display_name' => 'Hapus Ebook Bonus', 'src' => 'delete_pemesanantoko'],
        ];

        foreach ($permissions as $p) {
            // Buat permission jika belum ada
            $permId = DB::table('permissions')->where('name', $p['name'])->value('id');
            if (! $permId) {
                $permId = DB::table('permissions')->insertGetId([
                    'name' => $p['name'],
                    'display_name' => $p['display_name'],
                    'group' => 'ebook',
                    'description' => $p['display_name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Berikan ke setiap role yang sudah punya permission pemesanantoko padanannya
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
            ->whereIn('name', ['view_ebook', 'create_ebook', 'edit_ebook', 'delete_ebook'])
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
