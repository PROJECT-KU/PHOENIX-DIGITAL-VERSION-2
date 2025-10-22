<?php

namespace App\Livewire\Pages\Admin\Loan;

use App\Models\Loan;
use App\Models\User;
use Livewire\Component;

class LoanForm extends Component
{
    public $loanId = null;
    public $user_id;
    public $tanggal_peminjam;
    public $nominal = ''; // simpan sementara sebagai string (angka murni)
    public $deskripsi;
    public $status = 'pending';

    public $mode = 'create'; // 'create' | 'edit'

    protected function rules()
    {
        return [
            'user_id'          => 'required|exists:users,id',
            'tanggal_peminjam' => 'required|date',
            'nominal'          => 'required|numeric|min:0',
            'deskripsi'        => 'nullable|string',
            'status'           => 'required|in:pending,berjalan,lunas',
        ];
    }

    protected function messages()
    {
        return [
            'user_id.required'          => 'Nama peminjam harus dipilih.',
            'user_id.exists'            => 'Peminjam tidak ditemukan.',
            'tanggal_peminjam.required' => 'Tanggal pinjam harus diisi.',
            'tanggal_peminjam.date'     => 'Format tanggal tidak valid.',
            'nominal.required'          => 'Nominal harus diisi.',
            'nominal.numeric'           => 'Nominal harus berupa angka.',
            'nominal.min'               => 'Nominal tidak boleh kurang dari 0.',
            'status.required'           => 'Status harus dipilih.',
            'status.in'                 => 'Status tidak valid.',
        ];
    }

    public function mount($loanId = null)
    {
        if ($loanId) {
            $this->mode = 'edit';
            $this->loanId = $loanId;
            $this->loadLoan();
        } else {
            $this->tanggal_peminjam = now()->format('Y-m-d');
            $this->nominal = ''; // kosong saat create
        }
    }

    private function loadLoan()
    {
        $loan = Loan::findOrFail($this->loanId);

        $this->user_id          = $loan->user_id;
        $this->tanggal_peminjam = $loan->tanggal_peminjam->format('Y-m-d');
        $this->nominal          = (string) intval($loan->nominal);
        $this->deskripsi        = $loan->deskripsi;
        $this->status           = $loan->status;
    }

    public function save()
    {
        // Pastikan nominal angka murni
        $raw = preg_replace('/[^0-9]/', '', (string) $this->nominal);
        $this->nominal = $raw !== '' ? (int) $raw : null;

        $this->validate();

        try {
            $namaPeminjam = User::find($this->user_id)->name ?? null;

            if ($this->mode === 'edit') {
                $loan = Loan::findOrFail($this->loanId);
                $loan->update([
                    'user_id'          => $this->user_id,
                    'nama_peminjam'    => $namaPeminjam,
                    'tanggal_peminjam' => $this->tanggal_peminjam,
                    'nominal'          => $this->nominal,
                    'deskripsi'        => $this->deskripsi,
                    'status'           => $this->status,
                ]);

                session()->flash('success', 'Data Peminjaman berhasil diperbarui!');
                $this->dispatch('success-edit-loan');
            } else {
                Loan::create([
                    'user_id'          => $this->user_id,
                    'nama_peminjam'    => $namaPeminjam,
                    'tanggal_peminjam' => $this->tanggal_peminjam,
                    'nominal'          => $this->nominal,
                    'deskripsi'        => $this->deskripsi,
                    'status'           => $this->status,
                ]);

                session()->flash('success', 'Data Peminjaman berhasil ditambahkan!');
                $this->dispatch('success-add-loan');
            }

            $this->resetForm();

            return redirect()->route('admin.loan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan data Peminjaman: ' . $e->getMessage());
            $this->dispatch('failed-add-loan');
        }
    }

    private function resetForm()
    {
        $this->loanId = null;
        $this->user_id = null;
        $this->tanggal_peminjam = now()->format('Y-m-d');
        $this->nominal = '';
        $this->deskripsi = null;
        $this->status = 'pending';
        $this->mode = 'create';
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'berjalan', 'lunas'];

        return view('livewire.pages.admin.loan.loan-form', [
            'users' => $users,
            'statusOptions' => $statusOptions,
        ]);
    }
}
