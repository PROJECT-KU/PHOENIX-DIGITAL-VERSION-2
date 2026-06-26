<?php

namespace App\Livewire\Pages\Admin\Permission;

use App\Models\Permission;
use Livewire\Component;

class PermissionForm extends Component
{
    public ?Permission $permission = null;

    public $name = '';

    public $display_name = '';

    public $group = '';

    public $description = '';

    // Untuk mode edit
    public $isEdit = false;

    // Mode cepat (create): pilih modul + aksi -> name/group/display_name otomatis
    public $selectedModule = '';

    public $selectedAction = '';

    /**
     * Daftar modul mengikuti sidebar (key group => label tampilan).
     * Memudahkan admin membuat permission tanpa salah ketik key.
     */
    public array $sidebarModules = [
        'dashboard' => 'Dashboard',
        'pesananrsc' => 'Pesanan RSC',
        'pemesanantoko' => 'Pesanan Toko',
        'banners' => 'Banner',
        'customer_message' => 'Pesan Pelanggan',
        'promo' => 'Promo',
        'dataakun' => 'Data Akun',
        'product' => 'Produk',
        'bundlings' => 'Produk Bundling',
        'customer' => 'Pelanggan',
        'cashflow' => 'Cashflow',
        'spending' => 'Pengeluaran',
        'loan' => 'Peminjaman',
        'gajikaryawan' => 'Gaji Karyawan',
        'roles' => 'Role',
        'permission' => 'Permission',
        'users' => 'User',
        'karyawan' => 'Karyawan',
        'lowongan' => 'Lowongan Kerja',
        'pelamar' => 'Pelamar',
        'message' => 'Pesan Masuk',
    ];

    /**
     * Jenis aksi (prefix key => label tampilan).
     */
    public array $aksiOptions = [
        'view' => 'Lihat',
        'view_all' => 'Lihat Semua',
        'create' => 'Tambah',
        'edit' => 'Edit',
        'delete' => 'Hapus',
    ];

    protected $rules = [
        'name' => 'required|string|max:255|unique:permissions,name',
        'display_name' => 'required|string|max:255',
        'group' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'name.required' => 'Nama permission harus diisi',
        'name.unique' => 'Nama permission sudah digunakan',
        'display_name.required' => 'Nama tampilan harus diisi',
    ];

    public function mount($permission = null)
    {
        if ($permission) {
            $this->permission = $permission;
            $this->name = $this->permission->name ?? '';
            $this->display_name = $this->permission->display_name ?? '';
            $this->group = $this->permission->group ?? '';
            $this->description = $this->permission->description ?? '';
            $this->isEdit = true;
        }
    }

    public function updated($propertyName)
    {
        // Jangan validasi prop bantu (module/action) yang tidak punya rule.
        if (in_array($propertyName, ['selectedModule', 'selectedAction'])) {
            $this->generateFromSelection();

            return;
        }

        $this->validateOnly($propertyName);
    }

    /**
     * Isi otomatis name, group, dan display_name dari pilihan modul + aksi.
     * view_all -> name "view_all_{modul}", lainnya "{aksi}_{modul}".
     */
    public function generateFromSelection(): void
    {
        if (! $this->selectedModule || ! $this->selectedAction) {
            return;
        }

        $modul = $this->selectedModule;
        $aksi = $this->selectedAction;

        $this->name = $aksi === 'view_all'
            ? 'view_all_'.$modul
            : $aksi.'_'.$modul;

        $this->group = $modul;

        $labelModul = $this->sidebarModules[$modul] ?? ucwords(str_replace('_', ' ', $modul));
        $labelAksi = $this->aksiOptions[$aksi] ?? ucfirst($aksi);
        $this->display_name = $labelAksi.' '.$labelModul;
    }

    public function save()
    {
        if ($this->isEdit) {
            $this->rules['name'] = 'required|string|max:255|unique:permissions,name,'.$this->permission->id;
        }

        $this->validate();

        try {
            if ($this->isEdit) {
                $this->permission->update([
                    'name' => $this->name,
                    'display_name' => $this->display_name,
                    'group' => $this->group ?: null,
                    'description' => $this->description ?: null,
                ]);

                session()->flash('success', 'Permission berhasil diupdate');
            } else {
                Permission::create([
                    'name' => $this->name,
                    'display_name' => $this->display_name,
                    'group' => $this->group ?: null,
                    'description' => $this->description ?: null,
                ]);

                session()->flash('success', 'Permission berhasil ditambahkan');
            }

            return redirect()->route('admin.account.permission');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.permission.permission-form');
    }
}
