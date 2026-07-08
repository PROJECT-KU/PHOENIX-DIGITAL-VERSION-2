<?php

namespace App\Livewire\Pages\Admin\Spending;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\Spending;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public $product_id;

    public $durasi_value;

    public $durasi_type = 'bulan';

    public $selectedProductTipe = null; // 'sharing' | 'private'

    public $isEdit = false;

    public function updatedProductId($value)
    {
        $this->selectedProductTipe = $value ? (\App\Models\Product::find($value)?->tipe_akun) : null;
        // Durasi hanya untuk produk PRIVATE
        if ($this->selectedProductTipe !== 'private') {
            $this->durasi_value = null;
            $this->resetErrorBag(['durasi_value', 'durasi_type']);
        }
    }

    public function updatedNominal($value)
    {
        $this->nominal = (int) preg_replace('/[^0-9]/', '', $value);
    }

    public function updatedJenisPengeluaran($value)
    {
        // PIC Pembeli & Produk hanya relevan untuk pembelian akun
        if ($value !== 'pembelian_akun') {
            $this->pic_pembeli_id = null;
            $this->product_id = null;
            $this->resetErrorBag(['pic_pembeli_id', 'product_id']);
        }
    }

    protected function rules()
    {
        return [
            'tanggal_transaksi' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:pending,completed',
            'jenis_pengeluaran' => 'required|in:pembelian_akun,lainnya',
            // PIC Pembeli hanya wajib ketika jenis pengeluaran = pembelian akun
            'pic_pembeli_id' => $this->jenis_pengeluaran === 'pembelian_akun'
                ? 'required|exists:users,id'
                : 'nullable',
            'product_id' => $this->jenis_pengeluaran === 'pembelian_akun'
                ? 'required|exists:products,id'
                : 'nullable',
            // Durasi wajib hanya untuk pembelian akun produk PRIVATE (modal satuan per durasi)
            'durasi_value' => ($this->jenis_pengeluaran === 'pembelian_akun' && $this->selectedProductTipe === 'private')
                ? 'required|integer|min:1'
                : 'nullable',
            'durasi_type' => ($this->jenis_pengeluaran === 'pembelian_akun' && $this->selectedProductTipe === 'private')
                ? 'required|in:bulan,tahun'
                : 'nullable',
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
            'product_id.required' => 'Produk harus dipilih untuk pembelian akun.',
            'product_id.exists' => 'Produk tidak valid.',
            'durasi_value.required' => 'Durasi harus diisi untuk akun private.',
            'durasi_value.integer' => 'Durasi harus berupa angka.',
            'durasi_value.min' => 'Durasi minimal 1.',
            'durasi_type.required' => 'Satuan durasi harus dipilih.',
            'durasi_type.in' => 'Satuan durasi tidak valid.',
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
        $this->product_id = $spending->product_id;
        $this->durasi_value = $spending->durasi_value;
        $this->durasi_type = $spending->durasi_type ?: 'bulan';
        $this->selectedProductTipe = $spending->product?->tipe_akun;
    }

    public function save(SyncCashFlowAction $syncCashFlow)
    {
        $this->validate();

        // PIC Pembeli & Produk hanya disimpan untuk pembelian akun; selain itu null
        $picPembeliId = $this->jenis_pengeluaran === 'pembelian_akun' ? $this->pic_pembeli_id : null;
        $productId = $this->jenis_pengeluaran === 'pembelian_akun' ? $this->product_id : null;
        // Durasi hanya untuk pembelian akun produk PRIVATE
        $isPrivate = $this->jenis_pengeluaran === 'pembelian_akun' && $this->selectedProductTipe === 'private';
        $durasiValue = $isPrivate ? (int) $this->durasi_value : null;
        $durasiType = $isPrivate ? $this->durasi_type : null;

        try {
            DB::transaction(function () use ($syncCashFlow, $picPembeliId, $productId, $durasiValue, $durasiType) {

                if ($this->isEdit) {
                    $spending = Spending::findOrFail($this->spendingId);
                    $spending->update([
                        'tanggal_transaksi' => $this->tanggal_transaksi,
                        'nominal' => $this->nominal,
                        'deskripsi' => $this->deskripsi,
                        'jenis_pengeluaran' => $this->jenis_pengeluaran,
                        'status' => $this->status,
                        'penginput_id' => auth()->id(),
                        'pic_pembeli_id' => $picPembeliId,
                        'product_id' => $productId,
                        'durasi_value' => $durasiValue,
                        'durasi_type' => $durasiType,
                    ]);
                    session()->flash('success', 'berhasil edit data pengeluaran');
                } else {
                    $spending = Spending::create([
                        'tanggal_transaksi' => $this->tanggal_transaksi,
                        'nominal' => $this->nominal,
                        'deskripsi' => $this->deskripsi,
                        'status' => $this->status,
                        'jenis_pengeluaran' => $this->jenis_pengeluaran,
                        'penginput_id' => auth()->id(),
                        'pic_pembeli_id' => $picPembeliId,
                        'product_id' => $productId,
                        'durasi_value' => $durasiValue,
                        'durasi_type' => $durasiType,
                    ]);

                    session()->flash('success', 'berhasil tambah data pengeluaran');
                }

                $syncCashFlow->execute($spending, [
                    'amount' => $spending->nominal,
                    'type' => 'expense', // Pinjaman = Uang Keluar (Expense)
                    'date' => $spending->tanggal_transaksi,
                    'category' => 'Pengeluaran',
                    'description' => $spending->deskripsi ?? 'pengeluaran perusahaan',
                ]);
            });

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
        // Private dikelola di "Harga Modal Akun" & dicatat per-order; pembelian akun manual hanya untuk sharing.
        $products = \App\Models\Product::where(fn ($q) => $q->where('tipe_akun', '!=', 'private')->orWhereNull('tipe_akun'))
            ->select('id', 'nama_akun', 'tipe_akun')->orderBy('nama_akun')->get();

        return view('livewire.pages.admin.spending.spending-form', ['users' => $users, 'statusOptions' => $statusOptions, 'jenisPengeluaran' => $jenisPengeluaran, 'products' => $products]);
    }
}
