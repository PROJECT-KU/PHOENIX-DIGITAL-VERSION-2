<?php

namespace App\Livewire\Pages\Admin\Spending;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\Spending;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SpendingForm extends Component
{
    use WithFileUploads;

    public $spendingId = null;

    public $tanggal_transaksi;

    public $nominal;

    public $deskripsi;

    // Gambar/bukti pengeluaran (bisa lebih dari satu, opsional).
    // $fotosLama = path yang sudah tersimpan (mode edit) yang dipertahankan.
    // $fotosBaru = file baru (upload/kamera) yang belum tersimpan.
    // $tempUpload = penampung sementara input file (multiple) sebelum dipindah ke $fotosBaru.
    public array $fotosLama = [];

    public array $fotosBaru = [];

    public $tempUpload = [];

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
            'tempUpload.*' => 'nullable|image|max:4096',
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
        $this->fotosLama = $spending->images; // array path gambar tersimpan
    }

    /**
     * Setiap kali file dipilih (input multiple), pindahkan ke daftar $fotosBaru
     * agar terakumulasi (bisa pilih beberapa kali + kamera), lalu kosongkan input.
     */
    public function updatedTempUpload()
    {
        $this->validate(['tempUpload.*' => 'nullable|image|max:4096']);

        foreach ($this->tempUpload as $file) {
            $this->fotosBaru[] = $file;
        }

        $this->tempUpload = [];
    }

    /**
     * Hapus salah satu gambar yang SUDAH tersimpan (file fisik dihapus saat simpan).
     */
    public function removeFotoLama(int $index): void
    {
        unset($this->fotosLama[$index]);
        $this->fotosLama = array_values($this->fotosLama);
    }

    /**
     * Hapus salah satu gambar baru yang belum tersimpan.
     */
    public function removeFotoBaru(int $index): void
    {
        unset($this->fotosBaru[$index]);
        $this->fotosBaru = array_values($this->fotosBaru);
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

        // Susun daftar gambar akhir: foto lama yang dipertahankan + foto baru disimpan.
        $paths = array_values($this->fotosLama);
        foreach ($this->fotosBaru as $file) {
            if ($file && ! is_string($file)) {
                $paths[] = $file->store('spending', 'public');
            }
        }

        // Hapus file gambar lama yang dibuang (ada di data, tak lagi dipertahankan).
        if ($this->isEdit) {
            $original = Spending::find($this->spendingId)?->images ?? [];
            foreach (array_diff($original, $paths) as $dibuang) {
                Storage::disk('public')->delete($dibuang);
            }
        }

        // Kolom "gambar" menyimpan gambar pertama (kompatibilitas thumbnail),
        // "gambar_list" menyimpan seluruh daftar.
        $gambarPath = $paths[0] ?? null;
        $gambarList = $paths ?: null;

        try {
            DB::transaction(function () use ($syncCashFlow, $picPembeliId, $productId, $durasiValue, $durasiType, $gambarPath, $gambarList) {

                if ($this->isEdit) {
                    $spending = Spending::findOrFail($this->spendingId);
                    $spending->update([
                        'tanggal_transaksi' => $this->tanggal_transaksi,
                        'nominal' => $this->nominal,
                        'deskripsi' => $this->deskripsi,
                        'gambar' => $gambarPath,
                        'gambar_list' => $gambarList,
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
                        'gambar' => $gambarPath,
                        'gambar_list' => $gambarList,
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
