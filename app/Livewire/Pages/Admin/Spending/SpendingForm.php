<?php

namespace App\Livewire\Pages\Admin\Spending;

use App\Models\Spending;
use App\Models\User;
use Livewire\Component;

class SpendingForm extends Component
{
    public $spendingId = null;
    public $tanggal_transaksi;
    public $nominal;
    public $deskripsi;
    public $status = 'pending';
    public $jenis_pengeluaran = 'lainnya';
    public $pic_pembeli_id;

    public $isEdit = false;

    public function updatedNominal($value)
    {
        $this->nominal = (int) preg_replace('/[^0-9]/', '', $value);
    }

    protected function rules()
    {
        return [
            'tanggal_transaksi' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:pending,completed',
            'jenis_pengeluaran' => 'required|in:pembelian_akun,lainnya',
            'pic_pembeli_id' => 'nullable|exists:users,id',
        ];
    }

    protected function messages()
    {
        return [
            'tanggal_transaksi.required' => 'Tanggal transaksi harus diisi.',
            'tanggal_transaksi.date' => 'Format tanggal tidak valid.',
            'nominal.required' => 'Nominal harus diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal tidak boleh kurang dari 0.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status tidak valid.',
            'jenis_pengeluaran.required' => 'jenis pengeluaran harus dipilih.',
            'jenis_pengeluaran.in' => 'jenis pengeluaran tidak valid.',
            'pic_pembeli_id.required' => 'PIC Pembeli harus dipilih.',
            'pic_pembeli_id.exists' => 'PIC Pembeli tidak valid.',
        ];
    }

    public function mount($spendingId = null)
    {
        if ($spendingId) {
            $this->isEdit = true;
            $this->spendingId = $spendingId;
            $this->loadSpending();
        } else {
            $this->tanggal_transaksi = now()->format('Y-m-d');
        }
    }

    public function loadSpending()
    {
        $spending = Spending::findOrFail($this->spendingId);

        $this->tanggal_transaksi = $spending->tanggal_transaksi->format('Y-m-d');
        $this->nominal = $spending->nominal;
        $this->deskripsi = $spending->deskripsi;
        $this->status = $spending->status;
        $this->jenis_pengeluaran = $spending->jenis_pengeluaran;
        $this->pic_pembeli_id = $spending->pic_pembeli_id;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEdit) {
                $spending = Spending::findOrFail($this->spendingId);
                $spending->update([
                    'tanggal_transaksi' => $this->tanggal_transaksi,
                    'nominal' => $this->nominal,
                    'deskripsi' => $this->deskripsi,
                    'jenis_pengeluaran' => $this->jenis_pengeluaran,
                    'status' => $this->status,
                    'penginput_id' => auth()->id(),
                    'pic_pembeli_id' => $this->pic_pembeli_id,
                ]);
                session()->flash('success', 'berhasil edit data pengeluaran');
            } else {
                Spending::create([
                    'tanggal_transaksi' => $this->tanggal_transaksi,
                    'nominal' => $this->nominal,
                    'deskripsi' => $this->deskripsi,
                    'status' => $this->status,
                    'jenis_pengeluaran' => $this->jenis_pengeluaran,
                    'penginput_id' => auth()->id(),
                    'pic_pembeli_id' => $this->pic_pembeli_id,
                ]);

                session()->flash('success', 'berhasil tambah data pengeluaran');
            }
            return redirect()->route('admin.spending.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data.');
            $this->dispatch('failed-add-pengeluaran');
        }
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'completed'];
        $jenisPengeluaran = ['pembelian_akun', 'lainnya'];

        return view('livewire.pages.admin.spending.spending-form', ['users' => $users, 'statusOptions' => $statusOptions, 'jenisPengeluaran' => $jenisPengeluaran]);
    }
}
