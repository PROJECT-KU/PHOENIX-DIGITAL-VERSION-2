<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionAccessSeeder extends Seeder
{
    /**
     * Definisi modul: group => [label, daftar aksi yang tersedia].
     * Aksi: view, create, edit, delete.
     */
    private array $modules = [
        'dashboard' => ['Dashboard', ['view']],
        'pesananrsc' => ['Pesanan RSC', ['view', 'create', 'edit', 'delete']],
        'pemesanantoko' => ['Pesanan Toko', ['view', 'create', 'edit', 'delete']],
        'customer' => ['Pelanggan', ['view', 'create', 'edit', 'delete']],
        'customer_message' => ['Pesan Pelanggan', ['view', 'delete']],
        'banners' => ['Banner', ['view', 'create', 'edit', 'delete']],
        'promo' => ['Promo', ['view', 'create', 'edit', 'delete']],
        'dataakun' => ['Data Akun', ['view', 'create', 'edit', 'delete']],
        'product' => ['Produk', ['view', 'create', 'edit', 'delete']],
        'bundlings' => ['Produk Bundling', ['view', 'create', 'edit', 'delete']],
        'cashflow' => ['Cashflow', ['view']],
        'spending' => ['Pengeluaran', ['view', 'create', 'edit', 'delete']],
        'loan' => ['Peminjaman', ['view', 'create', 'edit', 'delete']],
        'gajikaryawan' => ['Gaji Karyawan', ['view', 'create', 'edit', 'delete']],
        'karyawan' => ['Karyawan', ['view', 'create', 'edit', 'delete']],
        'lowongan' => ['Lowongan Kerja', ['view', 'create', 'edit', 'delete']],
        'pelamar' => ['Pelamar', ['view', 'delete']],
        'message' => ['Pesan Masuk', ['view', 'delete']],
        'roles' => ['Role', ['view', 'create', 'edit', 'delete']],
        'permission' => ['Permission', ['view', 'create', 'edit', 'delete']],
    ];

    private array $aksiLabel = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Edit',
        'delete' => 'Hapus',
    ];

    public function run(): void
    {
        // 1. Buat katalog permission CRUD per modul
        foreach ($this->modules as $group => [$label, $aksiList]) {
            foreach ($aksiList as $aksi) {
                $name = $this->permName($group, $aksi);
                Permission::firstOrCreate(
                    ['name' => $name],
                    ['display_name' => $this->aksiLabel[$aksi].' '.$label, 'group' => $group]
                );
            }
        }

        // helper ambil id dari daftar nama
        $ids = fn (array $names) => Permission::whereIn('name', $names)->pluck('id')->all();

        // semua permission untuk daftar group tertentu (semua aksi yang tersedia)
        $allFor = function (array $groups): array {
            $names = [];
            foreach ($groups as $g) {
                foreach ($this->modules[$g][1] as $aksi) {
                    $names[] = $this->permName($g, $aksi);
                }
            }

            return $names;
        };

        // 2. admin = SEMUA permission
        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->permissions()->sync(Permission::pluck('id')->all());
        }

        // 3. admin-mimin = pesanan, pelanggan, pesan pelanggan, dashboard (CRUD penuh modulnya)
        if ($mimin = Role::where('name', 'admin-mimin')->first()) {
            $mimin->permissions()->syncWithoutDetaching($ids($allFor([
                'dashboard', 'pesananrsc', 'pemesanantoko', 'customer', 'customer_message',
            ])));
        }

        // 4. finance = dashboard + modul keuangan/konten (CRUD penuh), pesanantoko dipertahankan
        if ($finance = Role::where('name', 'finance')->first()) {
            $finance->permissions()->syncWithoutDetaching($ids($allFor([
                'dashboard', 'cashflow', 'pelamar', 'banners', 'promo', 'dataakun',
                'product', 'bundlings', 'spending', 'loan', 'gajikaryawan', 'lowongan',
                'message', 'pemesanantoko',
            ])));
        }
    }

    private function permName(string $group, string $aksi): string
    {
        // konsisten dengan permission lama: view_pemesanantoko, create_pemesanantoko, ...
        $suffix = $group === 'roles' ? 'roles' : $group;

        return $aksi.'_'.$suffix;
    }
}
