<?php

namespace App\Livewire\Pages\Admin\Karyawan;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class KaryawanEdit extends Component
{
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.karyawan.karyawan-edit');
    }
}
