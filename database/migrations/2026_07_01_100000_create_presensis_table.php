<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['hadir_offline', 'hadir_online', 'lembur']);

            $table->dateTime('waktu_masuk');
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('lng_masuk', 10, 7)->nullable();
            $table->unsignedInteger('jarak_masuk_meter')->nullable();

            $table->dateTime('waktu_pulang')->nullable();
            $table->decimal('lat_pulang', 10, 7)->nullable();
            $table->decimal('lng_pulang', 10, 7)->nullable();
            $table->unsignedInteger('jarak_pulang_meter')->nullable();

            $table->unsignedInteger('durasi_menit')->nullable();
            $table->string('status')->default('aktif'); // aktif | selesai
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'tanggal']);
        });

        // ===== Permissions presensi =====
        $now = now();
        $perms = [
            ['name' => 'view_presensi', 'display_name' => 'Presensi', 'group' => 'presensi', 'description' => 'Dapat mengakses & melakukan presensi sendiri'],
            ['name' => 'view_all_presensi', 'display_name' => 'Lihat Semua Presensi', 'group' => 'presensi', 'description' => 'Dapat melihat rekap presensi semua karyawan'],
            ['name' => 'manage_presensi_setting', 'display_name' => 'Kelola Pengaturan Presensi', 'group' => 'presensi', 'description' => 'Dapat mengatur lokasi, radius, & durasi presensi'],
        ];
        foreach ($perms as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p['name']],
                array_merge($p, ['updated_at' => $now, 'created_at' => $now])
            );
        }

        $permIds = DB::table('permissions')
            ->whereIn('name', ['view_presensi', 'view_all_presensi', 'manage_presensi_setting'])
            ->pluck('id', 'name');

        // Role admin/HR = yang punya view_karyawan; dapat semua permission presensi.
        // view_presensi diberikan ke semua role agar tiap karyawan bisa absen.
        $vkId = DB::table('permissions')->where('name', 'view_karyawan')->value('id');
        $adminRoleIds = $vkId
            ? DB::table('role_permission')->where('permission_id', $vkId)->pluck('role_id')
            : collect();

        foreach (DB::table('roles')->pluck('id') as $rid) {
            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $rid, 'permission_id' => $permIds['view_presensi']],
                ['created_at' => $now, 'updated_at' => $now]
            );
            if ($adminRoleIds->contains($rid)) {
                foreach (['view_all_presensi', 'manage_presensi_setting'] as $pn) {
                    DB::table('role_permission')->updateOrInsert(
                        ['role_id' => $rid, 'permission_id' => $permIds[$pn]],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }

        // ===== Default setting presensi =====
        $defaults = [
            'presensi_lokasi_nama' => 'Kantor Pusat',
            'presensi_lokasi_lat' => '',
            'presensi_lokasi_lng' => '',
            'presensi_radius_meter' => '300',
            'presensi_min_durasi_jam' => '6',
        ];
        foreach ($defaults as $k => $v) {
            DB::table('settings')->updateOrInsert(['key' => $k], ['value' => $v, 'updated_at' => $now, 'created_at' => $now]);
        }
    }

    public function down(): void
    {
        DB::table('role_permission')->whereIn('permission_id', function ($q) {
            $q->select('id')->from('permissions')
                ->whereIn('name', ['view_presensi', 'view_all_presensi', 'manage_presensi_setting']);
        })->delete();
        DB::table('permissions')->whereIn('name', ['view_presensi', 'view_all_presensi', 'manage_presensi_setting'])->delete();

        Schema::dropIfExists('presensis');
    }
};
