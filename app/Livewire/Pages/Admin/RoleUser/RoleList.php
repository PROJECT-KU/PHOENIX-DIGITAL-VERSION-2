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

    // modal peran
    public $showCreateRoleModalStatus = false;

    /*
     | Mengatur akses satu pengguna sudah PINDAH ke halaman sendiri
     | (App\Livewire\Pages\Admin\RoleUser\AksesAkun). Properti modalnya —
     | showEditModalStatus, selectedUser, izinTambahan, cariIzin, berikut
     | username/userEmail/userRole — ikut dibuang, bukan ditinggal menganggur:
     | properti yang tidak lagi dipakai tetap ikut terkirim bolak-balik di tiap
     | permintaan Livewire, dan yang membacanya kelak akan mengira layar ini
     | masih mengurusnya.
     */

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
        $this->showCreateRoleModalStatus = false;
        $this->roleIdBeingEdited = null;
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

        // izinTambahan ikut dimuat: daftar pengguna menandai siapa yang
        // punya izin pribadi, dan tanpa ini jadi satu kueri per baris.
        $users = User::with('role', 'izinTambahan')
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
