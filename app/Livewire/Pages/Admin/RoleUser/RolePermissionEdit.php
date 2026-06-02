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

        // Group permissions by group field
        $this->groupedPermissions = $permissions->groupBy('group')->map(function ($group) {
            return $group->sortBy('display_name');
        })->sortKeys();
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

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.role-user.role-permission-edit');
    }
}
