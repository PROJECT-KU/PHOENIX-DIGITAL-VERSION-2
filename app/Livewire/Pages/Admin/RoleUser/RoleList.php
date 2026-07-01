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
        $this->resetPage('rolePage');
        $this->resetPage('userPage');
    }

    public function updatedSearchRole()
    {
        $this->resetPage('rolePage');
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
        if (! auth()->user()->hasPermission('edit_roles')) {
            $this->dispatch('swal-alert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Anda tidak memiliki izin mengubah role user.']);

            return;
        }

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
        if (! auth()->user()->hasPermission('delete_roles')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus user.');

            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('swal-success', message: 'Data pengguna berhasil dihapus.');
    }

    public function updatedSearchUser()
    {
        $this->resetPage('userPage');
    }

    // manajemen role method
    public function addRole()
    {
        if (! auth()->user()->hasPermission('create_roles')) {
            $this->dispatch('swal-alert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Anda tidak memiliki izin menambah role.']);

            return;
        }

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
        if (! auth()->user()->hasPermission('edit_roles')) {
            $this->dispatch('swal-alert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Anda tidak memiliki izin mengubah role.']);

            return;
        }

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
        if (! auth()->user()->hasPermission('delete_roles')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus role.');

            return;
        }

        $role = ModelsRole::findOrFail($id);
        $role->delete();
        $this->dispatch('swal-success', message: 'Role berhasil dihapus.');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->roleIdBeingEdited = null;
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $roles = ModelsRole::withCount('permissions')->latest()
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchRole}%")
                    ->orWhere('description', 'like', "%{$this->searchRole}%");
            })
            ->paginate(10, ['*'], 'rolePage');

        $users = User::with('role')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchUser}%")
                    ->orWhere('email', 'like', "%{$this->searchUser}%");
            })
            ->paginate(10, ['*'], 'userPage');

        return view('livewire.pages.admin.role-user.role-list', [
            'roles' => $roles,
            'users' => $users,
        ]);
    }
}
