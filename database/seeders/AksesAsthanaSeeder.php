<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Asthana\ModeAsthana;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mode Asthana di lemon — padanan AksesOrchaSeeder.
 *
 *   php artisan db:seed --class=AksesAsthanaSeeder --force
 *
 * 1. Membuat izin gerbang 'akses_asthana'.
 * 2. Mengelompokkan izin situs, karyawan, lowongan, pelamar, dan pesan masuk
 *    ke grup 'asthana' (tampil satu blok di Role → Permission, seperti Orcha).
 * 3. Memberi 'akses_asthana' ke SETIAP peran/pengguna yang sudah memegang salah
 *    satu izin itu — tanpa ini mereka tiba-tiba terkunci dari layar yang
 *    kemarin masih bisa dibuka.
 *
 * Aman dijalankan berulang (firstOrCreate, syncWithoutDetaching).
 */
class AksesAsthanaSeeder extends Seeder
{
    public function run(): void
    {
        $akses = Permission::firstOrCreate(['name' => 'akses_asthana'], [
            'display_name' => 'Akses Mode Asthana',
            'group' => 'asthana',
            'description' => 'Dapat berpindah ke mode Asthana: situs perusahaan, karyawan, lowongan kerja, pelamar, dan pesan masuk. Izin per layar tetap berlaku di dalamnya.',
        ]);

        Permission::whereIn('name', ModeAsthana::IZIN)->update(['group' => 'asthana']);

        $terkait = Permission::whereIn('name', ModeAsthana::IZIN)
            ->where('name', '!=', 'akses_asthana')->pluck('id');

        Role::whereHas('permissions', fn ($q) => $q->whereIn('permissions.id', $terkait))
            ->get()
            ->each(fn (Role $r) => $r->permissions()->syncWithoutDetaching([$akses->id]));

        // Izin tambahan per orang (permission_user) juga ikut.
        DB::table('permission_user')->whereIn('permission_id', $terkait)
            ->distinct()->pluck('user_id')
            ->each(fn ($userId) => DB::table('permission_user')->insertOrIgnore([
                'user_id' => $userId,
                'permission_id' => $akses->id,
                'diberi_oleh' => 'Sistem (AksesAsthanaSeeder)',
                'alasan' => 'Otomatis: sudah memegang izin layar yang pindah ke mode Asthana',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
    }
}
