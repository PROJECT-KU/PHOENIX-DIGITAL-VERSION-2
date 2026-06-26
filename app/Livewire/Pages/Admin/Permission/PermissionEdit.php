<?php

namespace App\Livewire\Pages\Admin\Permission;

use App\Models\Permission;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PermissionEdit extends Component
{
    public Permission $permission;

    public function mount(Permission $permission)
    {
        $this->permission = $permission;
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.permission.permission-edit');
    }
}
