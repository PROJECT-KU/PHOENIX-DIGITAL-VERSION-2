<?php

namespace App\Livewire\Pages\Admin\RoleUser;

use App\Models\Role as ModelsRole;
use App\Models\User;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class RoleList extends Component
{
    use WithPagination;

    public string $activeTab = 'tab-role';

    #[Rule('required|string|max:255')]
    public $name;

    #[Rule('nullable|string|max:1000')]
    public $description;

    public $searchUser = '';

    public $searchRole = '';

    public $roleIdBeingEdited = null;

    // user
    public $username = '';

    public $userEmail = '';

    public $userRole = '';

    // modal
    public $showEditModalStatus = false;

    public $showCreateRoleModalStatus = false;

    public $selectedUser = null;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function cancelModal()
    {
        $this->showEditModalStatus = false;
        $this->selectedUser = null;
        $this->showCreateRoleModalStatus = false;
        $this->roleIdBeingEdited = null;
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

    public function showModalFormRole($id = null)
    {
        $this->showCreateRoleModalStatus = true;
        if ($id) {
            $this->roleIdBeingEdited = $id;
            $role = ModelsRole::findOrFail($id);
            $this->name = $role->name;
            $this->description = $role->description;
        }
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
        $this->dispatch('swal-alert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Role Pengguna berhasil diperbarui.',
        ]);
    }

    #[On('delete-user-data')]
    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        $this->dispatch('swal-alert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Data pengguna berhasil dihapus.',
        ]);
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
            $this->cancelModal();
            $this->resetForm();
            $this->dispatch('swal-alert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Role berhasil ditambahkan.',
            ]);
        } catch (Exception $e) {
            $this->cancelModal();
            $this->dispatch('swal-alert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Role gagal ditambahkan.',
            ]);
            $this->resetForm();
        }
    }

    public function updateRole()
    {
        $this->validate();

        $role = ModelsRole::findOrFail($this->roleIdBeingEdited);
        $role->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);
        $this->resetForm();
        $this->cancelModal();
        $this->dispatch('swal-alert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Role berhasil diperbarui.',
        ]);
    }

    #[On('delete-role-data')]
    public function deleteRole($id)
    {
        $role = ModelsRole::findOrFail($id);
        $role->delete();
        $this->dispatch('swal-alert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Role berhasil dihapus.',
        ]);
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
        $roles = ModelsRole::withCount('permissions')->latest()
            ->where('name', 'like', "%{$this->searchRole}%")
            ->get();

        $users = User::with('role')
            ->where('name', 'like', "%{$this->searchUser}%")
            ->orWhere('email', 'like', "%{$this->searchUser}%")
            ->paginate(5);

        return view('livewire.pages.admin.role-user.role-list', [
            'roles' => $roles,
            'users' => $users,
        ]);
    }
}
