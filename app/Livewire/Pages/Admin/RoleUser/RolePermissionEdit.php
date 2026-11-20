<?php

namespace App\Livewire\Pages\Admin\RoleUser;

use App\Models\Permission;
use App\Models\Role;
use App\Support\ModulIzin;
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
    /**
     * Metadata modul: urutan, label, ikon & warna mengikuti sidebar.
     *
     * Daftarnya tinggal di App\Support\ModulIzin supaya layar Akses Akun
     * per orang memakai nama yang sama persis — dulu layar itu menampilkan
     * nama grup mentahnya, dan modul baru selalu tertinggal di salah satu.
     *
     * Tetap sebagai properti publik karena blade-nya membaca $moduleMeta.
     */
    public array $moduleMeta = ModulIzin::META;

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

            session()->flash('success', 'Permission berhasil diperbarui untuk role: '.$this->role->name);

            return redirect()->route('admin.account.role');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui permission: '.$e->getMessage());
        }
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.role-user.role-permission-edit');
    }
}
