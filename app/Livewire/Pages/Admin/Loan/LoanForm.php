<?php

namespace App\Livewire\Pages\Admin\Loan;

use App\Models\Loan;
use App\Models\User;
use Livewire\Component;

class LoanForm extends Component
{
    public $loanId = null;
    public $nama_peminjam;
    public $tanggal_peminjam;
    public $nominal;
    public $deskripsi;
    public $status = 'pending';

    public $isEdit = false;

    protected function rules()
    {
        return [
            'nama_peminjam'     => 'required|string|max:255',
            'tanggal_peminjam'  => 'required|date',
            'nominal'           => 'required|numeric|min:0',
            'deskripsi'         => 'nullable|string|max:1000',
            'status'            => 'required|in:pending,berjalan,lunas',
        ];
    }

    protected function messages()
    {
        return [
            'nama_peminjam.required'    => 'Nama peminjam harus diisi.',
            'tanggal_peminjam.required' => 'Tanggal pinjam harus diisi.',
            'tanggal_peminjam.date'     => 'Format tanggal tidak valid.',
            'nominal.required'          => 'Nominal harus diisi.',
            'nominal.numeric'           => 'Nominal harus berupa angka.',
            'nominal.min'               => 'Nominal tidak boleh kurang dari 0.',
            'deskripsi.max'             => 'Deskripsi maksimal 1000 karakter.',
            'status.required'           => 'Status harus dipilih.',
            'status.in'                 => 'Status tidak valid.',
        ];
    }

    public function mount($loanId = null)
    {
        if ($loanId) {
            $this->isEdit = true;
            $this->loanId = $loanId;
            $this->loadLoan();
        } else {
            $this->tanggal_peminjam = now()->format('Y-m-d');
        }
    }

    public function loadLoan()
    {
        $loan = Loan::findOrFail($this->loanId);

        $this->nama_peminjam    = $loan->nama_peminjam;
        $this->tanggal_peminjam = $loan->tanggal_peminjam->format('Y-m-d');
        $this->nominal          = $loan->nominal;
        $this->deskripsi        = $loan->deskripsi;
        $this->status           = $loan->status;
    }

    public function save()
    {
        // Bersihkan nominal dari format Rp sebelum validasi
        $this->nominal = preg_replace('/[^0-9]/', '', $this->nominal);
        $this->nominal = $this->nominal ? (float) $this->nominal : 0;

        $this->validate([
            'nama_peminjam'    => 'required|string|max:255',
            'tanggal_peminjam' => 'required|date',
            'nominal'          => 'required|numeric|min:0',
            'deskripsi'        => 'nullable|string|max:1000',
            'status'           => 'required|in:pending,berjalan,lunas',
        ]);

        try {
            if ($this->isEdit) {
                $loan = Loan::findOrFail($this->loanId);
                $loan->update([
                    'nama_peminjam'    => $this->nama_peminjam,
                    'tanggal_peminjam' => $this->tanggal_peminjam,
                    'nominal'          => $this->nominal,
                    'deskripsi'        => $this->deskripsi,
                    'status'           => $this->status,
                    'user_id'          => auth()->id(),
                ]);

                $this->dispatch('success-edit-loan');
            } else {
                Loan::create([
                    'nama_peminjam'    => $this->nama_peminjam,
                    'tanggal_peminjam' => $this->tanggal_peminjam,
                    'nominal'          => $this->nominal,
                    'deskripsi'        => $this->deskripsi,
                    'status'           => $this->status,
                    'user_id'          => auth()->id(),
                ]);

                $this->dispatch('success-add-loan');
            }

            return redirect()->route('admin.loan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage()); // tampilkan error detail
            $this->dispatch('failed-add-loan');
        }
    }

    public function render()
    {
        // Ambil user untuk pilihan peminjam
        $users = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Status pinjaman
        $statusOptions = ['pending', 'berjalan', 'lunas'];

        return view('livewire.pages.admin.loan.loan-form', [
            'users' => $users,
            'statusOptions' => $statusOptions,
        ]);
    }
}
