<?php

namespace App\Livewire\Pages\Admin;

use App\Models\Role as ModelsRole;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Role extends Component
{
    #[Rule('required|string|max:255')]
    public $name;
    #[Rule('nullable|string|max:1000')]
    public $description;

    public $roleIdBeingEdited = null;

    public function addRole()
    {
        $this->validate();
        try {
            ModelsRole::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $this->resetForm();
            $this->dispatch('added-role');
        } catch (Exception $e) {
            $this->dispatch('failed-add-role');
        }
    }

    public function editRole($id)
    {
        $role = ModelsRole::findOrFail($id);

        $this->roleIdBeingEdited = $role->id;
        $this->name = $role->name;
        $this->description = $role->description;

        $this->dispatch('focus-input');
    }

    public function updateRole()
    {
        $this->validate();

        $role = ModelsRole::findOrFail($this->roleIdBeingEdited);
        $role->update([
            'name' => $this->name,
            'description' => $this->description
        ]);
        $this->resetForm();
        $this->dispatch('updated-role');
    }

    public function deleteRole($idRole)
    {
        $role = ModelsRole::findOrFail($idRole);
        $role->delete();
        $this->dispatch('deleted-role');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->roleIdBeingEdited = null;
    }
    #[Layout('layouts.app')]
    public function render()
    {
        $roles = ModelsRole::all();

        return view('livewire.pages.admin.role', [
            'roles' => $roles
        ]);
    }
}
