<?php

namespace App\Livewire\Pages\Admin\RoleUser;

use App\Models\Permission;
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

    /**
     * Izin TAMBAHAN yang sedang dicentang untuk pengguna yang dibuka.
     *
     * Peran tetap jadi dasar; ini pengecualian per orang. Dipakai saat
     * sebagian orang dalam satu peran boleh sesuatu dan sebagian tidak —
     * yang tanpa ini hanya bisa diselesaikan dengan menyalin perannya.
     *
     * @var array<int, int>
     */
    public array $izinTambahan = [];

    /**
     * Saringan daftar izin di modal.
     *
     * Ada 121 izin di aplikasi ini. Tanpa saringan, memberi satu izin Orcha
     * berarti menggulir melewati seluruh izin ebook, task, dan gaji dulu —
     * dan panel yang menuntut itu akan dilewati, lalu izinnya diberikan
     * lewat peran lagi seperti semula.
     */
    public string $cariIzin = '';

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
        $this->izinTambahan = [];
        $this->cariIzin = '';
        $this->showCreateRoleModalStatus = false;
        $this->roleIdBeingEdited = null;
    }

    public function showModalEdit($id)
    {
        $this->showEditModalStatus = true;
        $this->selectedUser = $id;

        $user = User::with('role', 'izinTambahan')->findOrFail($id);
        $this->username = $user->name;
        $this->userEmail = $user->email;
        // Pengguna tanpa peran tidak boleh merobohkan modalnya — sejak izin
        // bisa diberikan per orang, keadaan itu masuk akal.
        $this->userRole = $user->role?->id;
        $this->izinTambahan = $user->izinTambahan->pluck('id')->map(fn ($i) => (int) $i)->all();
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

        /*
         | Izin yang SUDAH datang dari perannya tidak ikut disimpan.
         |
         | Mencentangnya tidak salah, tetapi menyimpannya membuat baris
         | pribadi yang tidak mengubah apa pun — lalu saat izin itu dicabut
         | dari perannya kelak, orang ini diam-diam TETAP memilikinya, dan
         | tidak ada yang mengira harus memeriksa daftar pribadinya.
         */
        $dariPeran = $user->role
            ? $user->role->permissions->pluck('id')->map(fn ($i) => (int) $i)->all()
            : [];

        $pilihan = collect($this->izinTambahan)
            ->map(fn ($i) => (int) $i)
            ->reject(fn ($i) => in_array($i, $dariPeran, true))
            ->unique()
            ->values();

        $user->izinTambahan()->sync(
            $pilihan->mapWithKeys(fn ($id) => [$id => [
                // Siapa yang memberi — pertanyaan yang selalu muncul
                // belakangan, dan tidak bisa dijawab tabel berisi dua kunci.
                'diberi_oleh' => auth()->user()?->name,
            ]])->all()
        );

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

        // izinTambahan ikut dimuat: daftar pengguna menandai siapa yang
        // punya izin pribadi, dan tanpa ini jadi satu kueri per baris.
        $users = User::with('role', 'izinTambahan')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchUser}%")
                    ->orWhere('email', 'like', "%{$this->searchUser}%");
            })
            ->paginate(10, ['*'], 'userPage');

        /*
         | Izin yang bisa diberikan per orang, berikut yang SUDAH datang
         | dari perannya.
         |
         | Yang dari peran ditampilkan tercentang dan terkunci: tanpa itu,
         | daftar centang yang kosong terbaca sebagai "dia belum punya apa
         | pun" padahal perannya sudah memberinya sebelas izin.
         */
        $dariPeran = [];

        if ($this->selectedUser) {
            $dipilih = User::with('role.permissions')->find($this->selectedUser);
            $dariPeran = $dipilih?->role
                ? $dipilih->role->permissions->pluck('id')->map(fn ($i) => (int) $i)->all()
                : [];
        }

        return view('livewire.pages.admin.role-user.role-list', [
            'roles' => $roles,
            'users' => $users,
            'semuaIzin' => Permission::query()
                ->when(trim($this->cariIzin) !== '', function ($q) {
                    $kata = '%'.trim($this->cariIzin).'%';
                    // Nama teknis ikut dicari: yang mencari "akses_orcha"
                    // biasanya menyalinnya dari dokumentasi atau dari kode.
                    $q->where(fn ($w) => $w->where('name', 'like', $kata)
                        ->orWhere('display_name', 'like', $kata)
                        ->orWhere('group', 'like', $kata)
                        ->orWhere('description', 'like', $kata));
                })
                ->orderBy('group')->orderBy('name')->get()->groupBy('group'),
            'izinDariPeran' => $dariPeran,
        ]);
    }
}
