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
        if (! auth()->user()->hasPermission('delete_permission')) {
            $this->dispatch('swal-alert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Anda tidak memiliki izin menghapus permission.']);

            return;
        }

        try {
            $permission = Permission::findOrFail($id);

            // Check jika permission masih digunakan oleh role
            $usedByRoles = $permission->roles()->count();

            if ($usedByRoles > 0) {
                $this->dispatch('swal-confirm', [
                    'type' => 'error',
                    'title' => 'Gagal!',
                    'message' => 'Permission \'' . $permission->display_name . '\' masih digunakan oleh ' . $usedByRoles . ' role. Hapus dari role terlebih dahulu.',
                ]);

                return;
            }

            $permission->delete();
            $this->dispatch('swal-alert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Permission berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal-alert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Permission gagal dihapus.',
            ]);
        }
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $permisionsData = Permission::latest();

        if ($this->filterGroup) {
            $permisionsData->where('group', $this->filterGroup);
        }

        $permissions = $permisionsData
            ->where('display_name', 'like', "%{$this->search}%")
            ->paginate(10);

        $groups = Permission::query()
            ->whereNotNull('group')
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->get()
            ->map(fn($item) => [
                'value' => $item->group,
                'label' => $item->group,
            ])
            ->toArray();

        return view('livewire.pages.admin.permission.permission-list', [
            'permissions' => $permissions,
            'groups' => $groups,
            'totalPermission' => Permission::count(),
            'totalGroup' => count($groups),
        ]);
    }
}
