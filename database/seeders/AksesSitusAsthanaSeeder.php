<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Izin mengelola isi situs profil Asthana (portofolio, berita, klien,
 * testimoni, tim, angka).
 *
 *   php artisan db:seed --class=AksesSitusAsthanaSeeder
 *
 * Aman dijalankan berulang (firstOrCreate + syncWithoutDetaching).
 *
 * Langsung DIBAGIKAN ke peran yang sudah memegang view_lowongan: lowongan
 * adalah isi situs yang sama, dan operatornya orang yang sama. Membagikannya
 * lewat izin yang sudah dipegang — bukan lewat nama peran — karena nama peran
 * di produksi (Admin, administrator, karyawan) tidak sama dengan seeder.
 */
class AksesSitusAsthanaSeeder extends Seeder
{
    public function run(): void
    {
        $izin = Permission::firstOrCreate(['name' => 'kelola_situs_asthana'], [
            'display_name' => 'Kelola Isi Situs Asthana',
            'group' => 'asthana',
            'description' => 'Dapat menambah, mengubah, dan menghapus portofolio, berita, klien, testimoni, tim, dan angka di situs profil PT. Asthana Cipta Mandiri',
        ]);

        Role::whereHas('permissions', fn ($q) => $q->where('name', 'view_lowongan'))
            ->get()
            ->each(fn (Role $peran) => $peran->permissions()->syncWithoutDetaching([$izin->id]));
    }
}
