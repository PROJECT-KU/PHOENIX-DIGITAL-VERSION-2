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
        $this->validateOnly($propertyName);
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
