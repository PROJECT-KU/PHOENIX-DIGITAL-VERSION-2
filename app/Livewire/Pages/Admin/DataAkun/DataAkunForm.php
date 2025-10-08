<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use App\Models\DataAkun;
use App\Models\User;
use Livewire\Component;

class DataAkunForm extends Component
{
    public ?DataAkun $dataAkun = null;
    public $nama_akun = '';
    public $username_akun = '';
    public $password_akun = '';
    public $link_login_akun = '';
    public $pj_akun = '';
    public $harga_satuan = '';
    public $deskripsi = '';
    public $status = '';

    public $mode = 'create';

    public function mount($dataAkun = null)
    {
        if ($dataAkun) {
            $this->dataAkun = $dataAkun;
            $this->nama_akun = $this->dataAkun->nama_akun;
            $this->username_akun = $this->dataAkun->username_akun;
            $this->password_akun = $this->dataAkun->password_akun;
            $this->link_login_akun = $this->dataAkun->link_login_akun;
            $this->pj_akun = $this->dataAkun->pj_akun;
            $this->harga_satuan = $this->dataAkun->harga_satuan;
            $this->deskripsi = $this->dataAkun->deskripsi;
            $this->status = $dataAkun->status;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'nama_akun'      => 'required|min:3',
            'username_akun'  => 'required',
            'password_akun'  => 'required|min:6',
            'link_login_akun' => 'required|nullable|url',
            'pj_akun'        => 'required',
            'harga_satuan'        => 'required',
            'deskripsi'      => 'nullable|string',
            'status'          => 'required|in:active,non-active',
        ]);
        if ($this->mode === 'create') {
            $this->createDataAkun();
        } else {
            $this->updateDataAkun();
        }
    }
    private function createDataAkun()
    {
        try {
            DataAkun::create([
                'nama_akun'      => $this->nama_akun,
                'username_akun'  => $this->username_akun,
                'password_akun'  => $this->password_akun,
                'link_login_akun' => $this->link_login_akun,
                'pj_akun'        => $this->pj_akun,
                'harga_satuan'        => $this->harga_satuan,
                'deskripsi'      => $this->deskripsi,
                'status'          => $this->status,
            ]);

            session()->flash('success', 'Data Akun berhasil ditambahkan!');
            $this->dispatch('DataAkun-created');
            $this->resetForm();
            return redirect()->route('admin.DataAkun.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Data Akun: ' . $e->getMessage());
            $this->dispatch('failed-create-data-DataAkun');
        }
    }

    private function updateDataAkun()
    {
        try {
            $this->dataAkun->update([
                'nama_akun'      => $this->nama_akun,
                'username_akun'  => $this->username_akun,
                'password_akun'  => $this->password_akun,
                'link_login_akun' => $this->link_login_akun,
                'pj_akun'        => $this->pj_akun,
                'harga_satuan'        => $this->harga_satuan,
                'deskripsi'      => $this->deskripsi,
                'status'          => $this->status,
            ]);

            session()->flash('success', 'Perubahan Data Akun berhasil disimpan!');
            $this->dispatch('DataAkun-updated');
            $this->resetForm();
            return redirect()->route('admin.DataAkun.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate Data Akun: ' . $e->getMessage());
            $this->dispatch('failed-update-data-DataAkun');
        }
    }

    private function resetForm()
    {
        $this->nama_akun      = '';
        $this->username_akun  = '';
        $this->password_akun  = '';
        $this->link_login_akun = '';
        $this->pj_akun        = '';
        $this->harga_satuan        = '';
        $this->deskripsi      = '';
        $this->status        = '';
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('livewire.pages.admin.data-akun.DataAkun-form', [
            'dataAkun' => $this->dataAkun,
            'users'    => $users,
        ]);
    }
}
