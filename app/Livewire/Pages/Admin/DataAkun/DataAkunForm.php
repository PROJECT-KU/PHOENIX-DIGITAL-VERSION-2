<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use App\Models\DataAkun;
use App\Models\Product;
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

    /**
     * Semua slot nama akun dari produk.
     * Private -> 1 slot ("Nama 1"); Sharing -> 10 slot ("Nama 1".."Nama 10").
     */
    private function slotNames(): array
    {
        $names = [];
        foreach (Product::orderBy('nama_akun')->get(['id', 'nama_akun', 'tipe_akun']) as $p) {
            $base = trim((string) $p->nama_akun);
            if ($base === '') {
                continue;
            }
            $max = $p->tipe_akun === 'private' ? 1 : 10;
            for ($n = 1; $n <= $max; $n++) {
                $names[] = $base.' '.$n;
            }
        }

        return $names;
    }

    /**
     * Slot yang boleh dipilih: kecuali yang sedang dipakai Data Akun berstatus AKTIF.
     * Saat edit, nama record ini sendiri tetap boleh dipilih.
     */
    public function availableNames(): array
    {
        $used = DataAkun::where('status', 'active')->pluck('nama_akun')->all();
        $current = $this->mode === 'edit' ? $this->nama_akun : null;

        return array_values(array_filter(
            $this->slotNames(),
            fn ($nm) => ! in_array($nm, $used, true) || $nm === $current
        ));
    }

    public function save()
    {
        $this->validate([
            'nama_akun' => 'required|min:3',
            'username_akun' => 'required',
            'password_akun' => 'required|min:6',
            'link_login_akun' => 'required|nullable|url',
            'pj_akun' => 'required',
            'harga_satuan' => 'required',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:active,non-active',
        ]);

        // Cegah pilih nama yang sedang AKTIF dipakai record lain
        $dup = DataAkun::where('nama_akun', $this->nama_akun)->where('status', 'active');
        if ($this->mode === 'edit' && $this->dataAkun) {
            $dup->where('id', '!=', $this->dataAkun->id);
        }
        if ($dup->exists()) {
            $this->addError('nama_akun', 'Nama akun ini sedang AKTIF dipakai. Nonaktifkan yang lama dulu, atau pilih nama lain.');

            return;
        }

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
                'nama_akun' => $this->nama_akun,
                'username_akun' => $this->username_akun,
                'password_akun' => $this->password_akun,
                'link_login_akun' => $this->link_login_akun,
                'pj_akun' => $this->pj_akun,
                'harga_satuan' => $this->harga_satuan,
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
            ]);

            session()->flash('successCreated', 'Data Akun berhasil ditambahkan!');
            $this->dispatch('DataAkun-created');
            $this->resetForm();

            return redirect()->route('admin.DataAkun.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Akun: ' . $e->getMessage());
            $this->dispatch('failed-create-data-DataAkun');
        }
    }

    private function updateDataAkun()
    {
        try {
            $this->dataAkun->update([
                'nama_akun' => $this->nama_akun,
                'username_akun' => $this->username_akun,
                'password_akun' => $this->password_akun,
                'link_login_akun' => $this->link_login_akun,
                'pj_akun' => $this->pj_akun,
                'harga_satuan' => $this->harga_satuan,
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
            ]);

            session()->flash('successUpdated', 'Perubahan Data Akun berhasil disimpan!');
            $this->dispatch('DataAkun-updated');
            $this->resetForm();

            return redirect()->route('admin.DataAkun.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Akun: ' . $e->getMessage());
            $this->dispatch('failed-update-data-DataAkun');
        }
    }

    private function resetForm()
    {
        $this->nama_akun = '';
        $this->username_akun = '';
        $this->password_akun = '';
        $this->link_login_akun = '';
        $this->pj_akun = '';
        $this->harga_satuan = '';
        $this->deskripsi = '';
        $this->status = '';
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('livewire.pages.admin.data-akun.DataAkun-form', [
            'dataAkun' => $this->dataAkun,
            'users' => $users,
            'availableNames' => $this->availableNames(),
        ]);
    }
}
