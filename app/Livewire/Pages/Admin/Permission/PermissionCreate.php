<?php

namespace App\Livewire\Pages\Admin\Permission;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PermissionCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.permission.permission-create');
    }
}
