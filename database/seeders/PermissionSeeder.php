<?php

namespace Database\Seeders;

use App\Models\Permission;
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
                'description' => 'Dapat melihat pustaka ebook bonus',
            ],
            [
                'name' => 'create_ebook',
                'display_name' => 'Buat Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat menambah ebook bonus',
            ],
            [
                'name' => 'edit_ebook',
                'display_name' => 'Edit Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat mengedit ebook bonus',
            ],
            [
                'name' => 'delete_ebook',
                'display_name' => 'Hapus Ebook Bonus',
                'group' => 'ebook',
                'description' => 'Dapat menghapus ebook bonus',
            ],

            // Pemesanan Toko
            [
                'name' => 'view_pemesanantoko',
                'display_name' => 'Lihat Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat melihat halaman pemesanan toko',
            ],
            [
                'name' => 'create_pemesanantoko',
                'display_name' => 'Buat Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat membuat pemesanan toko baru',
            ],
            [
                'name' => 'edit_pemesanantoko',
                'display_name' => 'Edit Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat mengedit pemesanan toko',
            ],
            [
                'name' => 'delete_pemesanantoko',
                'display_name' => 'Hapus Pemesanan Toko',
                'group' => 'pemesanantoko',
                'description' => 'Dapat menghapus pemesanan toko',
            ],

            // User Management
            [
                'name' => 'view_users',
                'display_name' => 'Lihat Daftar User',
                'group' => 'users',
                'description' => 'Dapat melihat daftar user',
            ],
            [
                'name' => 'create_users',
                'display_name' => 'Buat User',
                'group' => 'users',
                'description' => 'Dapat membuat user baru',
            ],
            [
                'name' => 'edit_users',
                'display_name' => 'Edit User',
                'group' => 'users',
                'description' => 'Dapat mengedit user',
            ],
            [
                'name' => 'delete_users',
                'display_name' => 'Hapus User',
                'group' => 'users',
                'description' => 'Dapat menghapus user',
            ],

            // Role Management
            [
                'name' => 'view_roles',
                'display_name' => 'Lihat Pengaturan Role',
                'group' => 'roles',
                'description' => 'Dapat melihat pengaturan role',
            ],
            [
                'name' => 'manage_roles',
                'display_name' => 'Kelola Role & Permission',
                'group' => 'roles',
                'description' => 'Dapat mengelola role dan permission',
            ],

            // Presensi
            [
                'name' => 'view_presensi',
                'display_name' => 'Presensi',
                'group' => 'presensi',
                'description' => 'Dapat mengakses & melakukan presensi sendiri',
            ],
            [
                'name' => 'view_all_presensi',
                'display_name' => 'Lihat Semua Presensi',
                'group' => 'presensi',
                'description' => 'Dapat melihat rekap presensi semua karyawan',
            ],
            [
                'name' => 'manage_presensi_setting',
                'display_name' => 'Kelola Pengaturan Presensi',
                'group' => 'presensi',
                'description' => 'Dapat mengatur lokasi, radius, & durasi presensi',
            ],
            [
                'name' => 'create_presensi_manual',
                'display_name' => 'Presensikan Manual',
                'group' => 'presensi',
                'description' => 'Dapat menginput presensi karyawan secara manual (tanpa batas jarak & waktu)',
            ],

            // Modal
            [
                'name' => 'view_modal',
                'display_name' => 'Lihat Modal',
                'group' => 'modal',
                'description' => 'Dapat melihat modal operasional & modal pembelian akun',
            ],
            [
                'name' => 'create_modal',
                'display_name' => 'Tambah Modal',
                'group' => 'modal',
                'description' => 'Dapat menambah modal operasional',
            ],
            [
                'name' => 'edit_modal',
                'display_name' => 'Edit Modal',
                'group' => 'modal',
                'description' => 'Dapat mengubah modal operasional',
            ],
            [
                'name' => 'delete_modal',
                'display_name' => 'Hapus Modal',
                'group' => 'modal',
                'description' => 'Dapat menghapus modal operasional',
            ],

            // Pemasukan Lainnya
            [
                'name' => 'view_pemasukan',
                'display_name' => 'Lihat Pemasukan Lainnya',
                'group' => 'pemasukan',
                'description' => 'Dapat melihat pemasukan lain di luar pemesanan',
            ],
            [
                'name' => 'create_pemasukan',
                'display_name' => 'Tambah Pemasukan Lainnya',
                'group' => 'pemasukan',
                'description' => 'Dapat menambah pemasukan lain',
            ],
            [
                'name' => 'edit_pemasukan',
                'display_name' => 'Edit Pemasukan Lainnya',
                'group' => 'pemasukan',
                'description' => 'Dapat mengubah pemasukan lain',
            ],
            [
                'name' => 'delete_pemasukan',
                'display_name' => 'Hapus Pemasukan Lainnya',
                'group' => 'pemasukan',
                'description' => 'Dapat menghapus pemasukan lain',
            ],

            // Task
            [
                'name' => 'assign_task',
                'display_name' => 'Beri Task ke Bawahan',
                'group' => 'task',
                'description' => 'Dapat memberi task kepada bawahan di Task Saya (butuh memiliki bawahan)',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
