<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Ebook Bonus
            [
                'name' => 'view_ebook',
                'display_name' => 'Lihat Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat melihat pustaka ebook bonus'
            ],
            [
                'name' => 'create_ebook',
                'display_name' => 'Buat Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat menambah ebook bonus'
            ],
            [
                'name' => 'edit_ebook',
                'display_name' => 'Edit Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat mengedit ebook bonus'
            ],
            [
                'name' => 'delete_ebook',
                'display_name' => 'Hapus Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat menghapus ebook bonus'
            ],

            // Pemesanan Toko
            [
                'name' => 'view_pemesanantoko',
                'display_name' => 'Lihat Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat melihat halaman pemesanan toko'
            ],
            [
                'name' => 'create_pemesanantoko',
                'display_name' => 'Buat Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat membuat pemesanan toko baru'
            ],
            [
                'name' => 'edit_pemesanantoko',
                'display_name' => 'Edit Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat mengedit pemesanan toko'
            ],
            [
                'name' => 'delete_pemesanantoko',
                'display_name' => 'Hapus Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat menghapus pemesanan toko'
            ],

            // User Management
            [
                'name' => 'view_users',
                'display_name' => 'Lihat Daftar User',
                'group' => 'users',
                'description' => 'Dapat melihat daftar user'
            ],
            [
                'name' => 'create_users',
                'display_name' => 'Buat User',
                'group' => 'users',
                'description' => 'Dapat membuat user baru'
            ],
            [
                'name' => 'edit_users',
                'display_name' => 'Edit User',
                'group' => 'users',
                'description' => 'Dapat mengedit user'
            ],
            [
                'name' => 'delete_users',
                'display_name' => 'Hapus User',
                'group' => 'users',
                'description' => 'Dapat menghapus user'
            ],

            // Role Management
            [
                'name' => 'view_roles',
                'display_name' => 'Lihat Pengaturan Role',
                'group' => 'roles',
                'description' => 'Dapat melihat pengaturan role'
            ],
            [
                'name' => 'manage_roles',
                'display_name' => 'Kelola Role & Permission',
                'group' => 'roles',
                'description' => 'Dapat mengelola role dan permission'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
