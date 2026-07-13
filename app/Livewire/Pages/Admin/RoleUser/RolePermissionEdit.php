<?php

namespace App\Livewire\Pages\Admin\RoleUser;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Component;

class RolePermissionEdit extends Component
{
    public Role $role;

    public $selectedPermissions = [];

    public $groupedPermissions = [];

    public $searchModul = '';

    /**
     * Modul yang berisi DATA PRIBADI karyawan (self-service).
     * Dipakai untuk menampilkan label penjelas di halaman edit permission
     * agar admin awam paham mana yang boleh diakses karyawan untuk dirinya.
     * Harus sinkron dengan $scopedModules di PermissionAccessSeeder.
     */
    public array $selfServiceModules = [
        'gajikaryawan',
        'loan',
        'dashboard',
    ];

    /**
     * Metadata modul: urutan, label, ikon & warna mengikuti sidebar.
     * key => [label, ikon bootstrap, gradient warna].
     */
    public array $moduleMeta = [
        // Dashboard
        'dashboard'        => ['Dashboard', 'bi-grid-fill', '#4f46e5,#6366f1'],
        // Pesanan
        'pesananrsc'       => ['Pesanan RSC', 'bi-cart', '#2563eb,#0ea5e9'],
        'pemesanantoko'    => ['Pesanan Toko', 'bi-cart-check', '#2563eb,#0ea5e9'],
        // E-Commerce
        'banners'          => ['Data Banner', 'bi-shop', '#0d9488,#14b8a6'],
        'productreview'    => ['Moderasi Ulasan Produk', 'bi-star-half', '#0d9488,#14b8a6'],
        'customer_message' => ['Pesan Masuk Pelanggan', 'bi-chat-dots', '#0d9488,#14b8a6'],
        // Promo
        'promo'            => ['Promo', 'bi-tag', '#d97706,#f59e0b'],
        // Produk
        'dataakun'         => ['Data Akun', 'bi-person-badge', '#7c3aed,#6d28d9'],
        'product'          => ['Product', 'bi-box', '#7c3aed,#6d28d9'],
        'bundlings'        => ['Product Bundling', 'bi-boxes', '#7c3aed,#6d28d9'],
        // Pelanggan
        'customer'         => ['Pelanggan', 'bi-people', '#0891b2,#06b6d4'],
        // Keuangan
        'cashflow'         => ['Cashflow', 'bi-cash-coin', '#059669,#10b981'],
        'spending'         => ['Pengeluaran', 'bi-cash-stack', '#059669,#10b981'],
        'loan'             => ['Peminjaman', 'bi-wallet2', '#059669,#10b981'],
        'gajikaryawan'     => ['Gaji Karyawan', 'bi-coin', '#059669,#10b981'],
        // Akun
        'roles'            => ['Pengaturan Role', 'bi-person-gear', '#e11d48,#f43f5e'],
        'permission'       => ['Permission Akun', 'bi-shield-lock', '#e11d48,#f43f5e'],
        'users'            => ['Data User', 'bi-person', '#e11d48,#f43f5e'],
        // Karyawan & Karir
        'karyawan'         => ['Karyawan', 'bi-person-vcard', '#db2777,#ec4899'],
        'lowongan'         => ['Lowongan Kerja', 'bi-briefcase', '#db2777,#ec4899'],
        'pelamar'          => ['Pelamar', 'bi-file-earmark-person', '#db2777,#ec4899'],
        'message'          => ['Pesan Masuk', 'bi-envelope', '#db2777,#ec4899'],
    ];

    public function mount(Role $role)
    {
        $this->role = $role;

        // Load permissions yang sudah dimiliki role
        $this->selectedPermissions = $role->permissions()->pluck('permissions.id')->toArray();

        // Group permissions berdasarkan group
        $this->loadGroupedPermissions();
    }

    public function loadGroupedPermissions()
    {
        $permissions = Permission::all();

        // Urutan modul mengikuti sidebar (key moduleMeta).
        $urutan = array_flip(array_keys($this->moduleMeta));

        // Group permissions by group field, urutkan tiap aksi, lalu urutkan grup sesuai sidebar.
        $this->groupedPermissions = $permissions->groupBy('group')
            ->map(fn ($group) => $group->sortBy('display_name'))
            ->sortBy(fn ($group, $key) => $urutan[$key] ?? 999);
    }

    public function togglePermission($permissionId)
    {
        if (in_array($permissionId, $this->selectedPermissions)) {
            // Remove permission
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permissionId]);
        } else {
            // Add permission
            $this->selectedPermissions[] = $permissionId;
        }
    }

    public function toggleGroup($groupName)
    {
        $groupPermissions = collect($this->groupedPermissions[$groupName] ?? [])->pluck('id')->toArray();

        // Check if all permissions in group are selected
        $allSelected = ! array_diff($groupPermissions, $this->selectedPermissions);

        if ($allSelected) {
            // Unselect all in group
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            // Select all in group
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }
    }

    public function isGroupFullySelected($groupName)
    {
        $groupPermissions = collect($this->groupedPermissions[$groupName] ?? [])->pluck('id')->toArray();

        return ! array_diff($groupPermissions, $this->selectedPermissions);
    }

    public function save()
    {
        try {
            // Sync permissions
            $this->role->permissions()->sync($this->selectedPermissions);

            session()->flash('success', 'Permission berhasil diperbarui untuk role: ' . $this->role->name);

            return redirect()->route('admin.account.role');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui permission: ' . $e->getMessage());
        }
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.role-user.role-permission-edit');
    }
}
