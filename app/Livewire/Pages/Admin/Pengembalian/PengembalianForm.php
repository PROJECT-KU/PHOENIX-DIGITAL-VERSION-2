<?php

namespace App\Livewire\Pages\Admin\Pengembalian;

use App\Models\User;
use Livewire\Component;
use App\Models\Pengembalian;

class PengembalianForm extends Component
{
    public $pengembalianId = null;
    public $user_id;
    public $tanggal_pengembalian;
    public $nominal = ''; // angka murni
    public $deskripsi;
    public $status = 'pending';

    public $mode = 'create'; // create | edit

    protected function rules()
    {
        return [
            'user_id'              => 'required|exists:users,id',
            'tanggal_pengembalian' => 'required|date',
            'nominal'              => 'required|numeric|min:0',
            'deskripsi'            => 'nullable|string',
            'status'               => 'required|in:pending,berjalan,lunas',
        ];
    }

    protected function messages()
    {
        return [
            'user_id.required'              => 'Nama penginput harus dipilih.',
            'user_id.exists'                => 'Penginput tidak ditemukan.',
            'tanggal_pengembalian.required' => 'Tanggal pengembalian harus diisi.',
            'tanggal_pengembalian.date'     => 'Format tanggal tidak valid.',
            'nominal.required'              => 'Nominal harus diisi.',
            'nominal.numeric'               => 'Nominal harus berupa angka.',
            'nominal.min'                   => 'Nominal tidak boleh kurang dari 0.',
            'status.required'               => 'Status harus dipilih.',
            'status.in'                     => 'Status tidak valid.',
        ];
    }

    public function mount($pengembalianId = null)
    {
        if ($pengembalianId) {
            $this->mode = 'edit';
            $this->pengembalianId = $pengembalianId;
            $this->loadPengembalian();
        } else {
            $this->tanggal_pengembalian = now()->format('Y-m-d');
            $this->nominal = '';
        }
    }

    private function loadPengembalian()
    {
        $pengembalian = Pengembalian::findOrFail($this->pengembalianId);

        $this->user_id              = $pengembalian->user_id;
        $this->tanggal_pengembalian = $pengembalian->tanggal_pengembalian->format('Y-m-d');
        $this->nominal              = (string) intval($pengembalian->nominal);
        $this->deskripsi            = $pengembalian->deskripsi;
        $this->status               = $pengembalian->status;
    }

    public function save()
    {
        // pastikan nominal angka murni
        $raw = preg_replace('/[^0-9]/', '', (string) $this->nominal);
        $this->nominal = $raw !== '' ? (int) $raw : null;

        $this->validate();

        try {
            // ambil nama user dari user_id
            $namaUser = User::find($this->user_id)?->name;

            if ($this->mode === 'edit') {
                $pengembalian = Pengembalian::findOrFail($this->pengembalianId);
                $pengembalian->update([
                    'user_id'              => $this->user_id,
                    'nama_pengembalian'    => $namaUser,
                    'tanggal_pengembalian' => $this->tanggal_pengembalian,
                    'nominal'              => $this->nominal,
                    'deskripsi'            => $this->deskripsi,
                    'status'               => $this->status,
                ]);

                session()->flash('success', 'Data Pengembalian berhasil diperbarui!');
                $this->dispatch('success-edit-pengembalian');
            } else {
                Pengembalian::create([
                    'user_id'              => $this->user_id,
                    'nama_pengembalian'    => $namaUser,
                    'tanggal_pengembalian' => $this->tanggal_pengembalian,
                    'nominal'              => $this->nominal,
                    'deskripsi'            => $this->deskripsi,
                    'status'               => $this->status,
                ]);

                session()->flash('success', 'Data Pengembalian berhasil ditambahkan!');
                $this->dispatch('success-add-pengembalian');
            }

            $this->resetForm();

            return redirect()->route('admin.pengembalian.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan data Pengembalian: ' . $e->getMessage());
            $this->dispatch('failed-add-pengembalian');
        }
    }

    private function resetForm()
    {
        $this->pengembalianId = null;
        $this->user_id = null;
        $this->tanggal_pengembalian = now()->format('Y-m-d');
        $this->nominal = '';
        $this->deskripsi = null;
        $this->status = 'pending';
        $this->mode = 'create';
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'berjalan', 'lunas'];

        return view('livewire.pages.admin.pengembalian.pengembalian-form', [
            'users' => $users,
            'statusOptions' => $statusOptions,
        ]);
    }
}
