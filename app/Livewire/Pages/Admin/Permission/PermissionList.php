<?php

namespace App\Livewire\Pages\Admin\Permission;

use App\Models\Permission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionList extends Component
{
    use WithPagination;

    public $search = '';

    public $filterGroup = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterGroup()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['filterGroup', 'search']);
        $this->resetPage();
    }

    #[On('delete-permission-data')]
    public function delete($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            // Check jika permission masih digunakan oleh role
            $usedByRoles = $permission->roles()->count();

            if ($usedByRoles > 0) {
                session()->flash('error', "Permission '{$permission->display_name}' masih digunakan oleh {$usedByRoles} role. Hapus dari role terlebih dahulu.");

                return;
            }

            $permission->delete();
            session()->flash('success', 'Permission berhasil dihapus');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $permisionsData = Permission::latest();

        if ($this->filterGroup) {
            $permisionsData->where('group', $this->filterGroup);
        }

        $permissions = $permisionsData
            ->where('display_name', 'like', "%{$this->search}%")
            ->paginate(8);

        return view('livewire.pages.admin.permission.permission-list', [
            'permissions' => $permissions,
            'groups' => Permission::query()
                ->whereNotNull('group')
                ->select('group')
                ->distinct()
                ->orderBy('group')
                ->get()
                ->map(fn ($item) => [
                    'value' => $item->group,
                    'label' => $item->group,
                ])
                ->toArray(),
        ]);
    }
}
