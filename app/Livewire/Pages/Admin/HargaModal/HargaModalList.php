<?php

namespace App\Livewire\Pages\Admin\HargaModal;

use App\Models\Product;
use App\Models\ProductModalPrice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class HargaModalList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public bool $showForm = false;

    public ?string $editingId = null;

    public $formProductId = '';

    public $formDurasiValue = 1;

    public $formDurasiType = 'bulan';

    public $formHarga = '';

    public $formBerlakuMulai = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Produk jasa: modal dicatat per SATUAN kerjanya, durasinya dikunci.
     *  - Parafrase (per halaman) → 1 halaman
     *  - Cek plagiasi (paket)    → 1 kali (per pengecekan)
     */
    public function updatedFormProductId($value): void
    {
        $p = $value ? Product::find($value) : null;
        if ($p && $p->butuh_file) {
            $this->formDurasiType = $p->jasaPerHalaman() ? 'halaman' : 'kali';
            $this->formDurasiValue = 1;
        } elseif (in_array($this->formDurasiType, ['kali', 'halaman'], true)) {
            // Pindah dari jasa ke non-jasa: kembalikan default.
            $this->formDurasiType = 'bulan';
            $this->formDurasiValue = 1;
        }
    }

    /** Apakah produk yang dipilih di form adalah produk jasa? */
    public function getFormIsJasaProperty(): bool
    {
        return $this->formProductId
            ? (bool) optional(Product::find($this->formProductId))->butuh_file
            : false;
    }

    /** Jasa per halaman? (label form: "per 1 halaman" vs "per 1× pengecekan") */
    public function getFormIsPerHalamanProperty(): bool
    {
        return $this->formProductId
            ? (bool) optional(Product::find($this->formProductId))->jasaPerHalaman()
            : false;
    }

    private function bolehKelola(): bool
    {
        return auth()->user()?->hasPermission('manage_harga_modal') ?? false;
    }

    private function toNumber($value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }

    public function openCreate(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('hm-error', message: 'Anda tidak punya izin.');

            return;
        }
        $this->reset(['editingId', 'formProductId', 'formDurasiValue', 'formDurasiType', 'formHarga']);
        $this->formDurasiValue = 1;
        $this->formDurasiType = 'bulan';
        $this->formBerlakuMulai = now()->toDateString();
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function openEdit($id): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('hm-error', message: 'Anda tidak punya izin.');

            return;
        }
        $p = ProductModalPrice::find($id);
        if (! $p) {
            $this->dispatch('hm-error', message: 'Data tidak ditemukan.');

            return;
        }
        $this->editingId = $p->id;
        $this->formProductId = $p->product_id;
        $this->formDurasiValue = $p->durasi_value;
        $this->formDurasiType = $p->durasi_type;
        // Baris jasa warisan bisa bersatuan lama ('kali' padahal produknya kini
        // per halaman) — tampilkan satuan yang benar sejak form dibuka.
        if ($p->product && $p->product->butuh_file) {
            $this->formDurasiType = $p->product->jasaPerHalaman() ? 'halaman' : 'kali';
            $this->formDurasiValue = 1;
        }
        $this->formHarga = number_format((int) $p->harga, 0, ',', '.');
        $this->formBerlakuMulai = $p->berlaku_mulai->toDateString();
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('hm-error', message: 'Anda tidak punya izin.');

            return;
        }

        $this->formHarga = (string) $this->toNumber($this->formHarga);

        // Produk jasa: paksa satuan kerjanya di sisi server, bukan hanya saat
        // produk dipilih. Tanpa ini, MENGEDIT baris lama (mis. warisan "1 kali")
        // pada produk parafrase akan tersimpan sebagai 'kali' dan modal per
        // halaman jadi tak terbaca (Rp0).
        $produk = $this->formProductId ? Product::find($this->formProductId) : null;
        if ($produk && $produk->butuh_file) {
            $this->formDurasiType = $produk->jasaPerHalaman() ? 'halaman' : 'kali';
            $this->formDurasiValue = 1;
        }

        $this->validate([
            'formProductId' => ['required', 'exists:products,id'],
            'formDurasiValue' => ['required', 'integer', 'min:1'],
            'formDurasiType' => ['required', 'in:bulan,tahun,kali,halaman'],
            'formHarga' => ['required', 'numeric', 'min:1'],
            'formBerlakuMulai' => ['required', 'date'],
        ], [], [
            'formProductId' => 'produk',
            'formDurasiValue' => 'durasi',
            'formDurasiType' => 'satuan durasi',
            'formHarga' => 'harga',
            'formBerlakuMulai' => 'berlaku mulai',
        ]);

        $data = [
            'product_id' => $this->formProductId,
            'durasi_value' => (int) $this->formDurasiValue,
            'durasi_type' => $this->formDurasiType,
            'harga' => $this->toNumber($this->formHarga),
            'berlaku_mulai' => $this->formBerlakuMulai,
        ];

        if ($this->editingId) {
            $p = ProductModalPrice::find($this->editingId);
            if (! $p) {
                $this->dispatch('hm-error', message: 'Data tidak ditemukan.');

                return;
            }
            $p->update($data);
        } else {
            ProductModalPrice::create($data);
        }

        $this->showForm = false;
        $this->reset(['editingId', 'formProductId', 'formHarga']);
        $this->resetPage();
        $this->dispatch('hm-saved');
    }

    public function deleteHarga($id): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('hm-deleteError', message: 'Anda tidak punya izin.');

            return;
        }
        $p = ProductModalPrice::find($id);
        if (! $p) {
            $this->dispatch('hm-deleteError', message: 'Data tidak ditemukan.');

            return;
        }
        $p->delete();
        $this->dispatch('hm-deleted');
    }

    #[Layout('livewire.layout.templateindex')]
    /**
     * Ubah nama bulan Indonesia → angka agar pencarian tanggal (mis. "Juni 2026") jalan.
     */
    protected function normalizeDateSearch(string $term): string
    {
        $bulan = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        ];

        $hasil = mb_strtolower(trim($term));
        foreach ($bulan as $nama => $angka) {
            $hasil = str_replace($nama, $angka, $hasil);
        }

        return preg_replace('/\s+/', ' ', $hasil);
    }

    public function render()
    {
        $prices = ProductModalPrice::query()
            ->with('product')
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $digits = preg_replace('/\D/', '', $this->search); // untuk harga & angka durasi
                $dateTerm = '%'.$this->normalizeDateSearch($this->search).'%';

                $q->where(function ($sub) use ($term, $digits, $dateTerm) {
                    $sub->whereHas('product', fn ($p) => $p->where('nama_akun', 'like', $term))
                        ->orWhere('durasi_type', 'like', $term)
                        ->orWhereRaw('CAST(durasi_value AS CHAR) LIKE ?', [$term])
                        ->orWhereRaw("CONCAT(durasi_value, ' ', durasi_type) LIKE ?", [$term])
                        ->orWhereRaw("DATE_FORMAT(berlaku_mulai, '%d %m %Y') LIKE ?", [$dateTerm])
                        ->orWhereRaw("DATE_FORMAT(berlaku_mulai, '%Y-%m-%d') LIKE ?", [$dateTerm]);

                    if ($digits !== '') {
                        $sub->orWhereRaw('CAST(harga AS CHAR) LIKE ?', ['%'.$digits.'%']);
                    }
                });
            })
            ->join('products', 'products.id', '=', 'product_modal_prices.product_id')
            ->orderBy('products.nama_akun')
            ->orderBy('product_modal_prices.durasi_type')
            ->orderBy('product_modal_prices.durasi_value')
            ->orderByDesc('product_modal_prices.berlaku_mulai')
            ->select('product_modal_prices.*')
            ->paginate(12);

        // Produk yang punya modal: private + jasa (butuh_file, modal per pengecekan).
        $products = Product::where('tipe_akun', 'private')
            ->orWhere('butuh_file', true)
            ->orderBy('nama_akun')
            ->get(['id', 'nama_akun', 'butuh_file']);

        return view('livewire.pages.admin.harga-modal.harga-modal-list', [
            'prices' => $prices,
            'products' => $products,
        ]);
    }
}
