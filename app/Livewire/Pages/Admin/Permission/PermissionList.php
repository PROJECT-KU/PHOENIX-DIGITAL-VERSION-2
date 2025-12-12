<?php

namespace App\Livewire\Pages\Admin\Permission;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionList extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render()
    {
        $permisions = Permission::query()
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.permission.permission-list', [
            'permissions' => $permisions
        ]);
    }
}
