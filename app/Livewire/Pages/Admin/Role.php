<?php

namespace App\Livewire\Pages\Admin;

use App\Models\Role as ModelsRole;
use App\Models\User;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Role extends Component
{
    use WithPagination;

    #[Rule('required|string|max:255')]
    public $name;
    #[Rule('nullable|string|max:1000')]
    public $description;

    public $searchUser = '';
    public $roleIdBeingEdited = null;

    // user
    public $username = '';
    public $userEmail = '';
    public $userRole = '';
    // modal
    public $showEditModalStatus = false;
    public $showDeleteModalStatus = false;
    public $selectedUser = null;

    // modal method
    public function confirmDelete($userId)
    {
        $this->selectedUser = User::find($userId);
        $this->showDeleteModalStatus = true;
    }
    public function cancelModal()
    {
        $this->showDeleteModalStatus = false;
        $this->showEditModalStatus = false;
        $this->selectedUser = null;
    }
    public function showModalEdit($id)
    {
        $this->showEditModalStatus = true;
        $this->selectedUser = $id;

        $user = User::with('role')->findOrFail($id);
        $this->username = $user->name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role->id;
    }
    public function updateRoleUser()
    {
        $this->validate([
            'userRole' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($this->selectedUser);
        $user->update([
            'role_id' => $this->userRole,
        ]);

        $this->cancelModal();
        $this->dispatch('user-role-updated');
    }
    public function deleteUser($idUser)
    {
        User::findOrFail($idUser)->delete();
        $this->cancelModal();
        $this->dispatch('user-deleted');
    }
    public function updatedSearchUser()
    {
        $this->resetPage();
    }

    // manajemen role method
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
        $users = User::with('role')
            ->where('name', 'like', "%{$this->searchUser}%")
            ->orWhere('email', 'like', "%{$this->searchUser}%")
            ->paginate(5);

        return view('livewire.pages.admin.role', [
            'roles' => $roles,
            'users' => $users
        ]);
    }
}
