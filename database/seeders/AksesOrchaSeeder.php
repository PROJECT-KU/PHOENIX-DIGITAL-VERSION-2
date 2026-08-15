<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Menambahkan permission akses Orcha Journey.
 *
 * Dipisah dari PermissionSeeder supaya aman dijalankan di server yang datanya
 * sudah ada — memakai firstOrCreate, jadi tidak menggandakan baris bila
 * dijalankan dua kali.
 *
 *   php artisan db:seed --class=AksesOrchaSeeder
 *
 * Setelah itu, berikan permission ini ke role yang berhak lewat
 * Akun → Role → Permission.
 */
class AksesOrchaSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            [
                'name' => 'akses_orcha',
                'display_name' => 'Akses Dashboard Orcha',
                'group' => 'orcha',
                'description' => 'Dapat berpindah ke dashboard Orcha Journey dan melihat pendaftaran, sewa, pembatalan, serta pesan yang masuk',
            ],
            [
                'name' => 'view_orcha_kesehatan',
                'display_name' => 'Lihat Riwayat Kesehatan Peserta Orcha',
                'group' => 'orcha',
                'description' => 'Dapat membuka riwayat kesehatan peserta open trip. Data pribadi — berikan hanya kepada yang benar-benar perlu',
            ],
        ];

        foreach ($daftar as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
