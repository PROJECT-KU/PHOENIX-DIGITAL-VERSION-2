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

        $this->validate([
            'formProductId' => ['required', 'exists:products,id'],
            'formDurasiValue' => ['required', 'integer', 'min:1'],
            'formDurasiType' => ['required', 'in:bulan,tahun'],
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
    public function render()
    {
        $prices = ProductModalPrice::query()
            ->with('product')
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $q->whereHas('product', fn ($p) => $p->where('nama_akun', 'like', $term));
            })
            ->join('products', 'products.id', '=', 'product_modal_prices.product_id')
            ->orderBy('products.nama_akun')
            ->orderBy('product_modal_prices.durasi_type')
            ->orderBy('product_modal_prices.durasi_value')
            ->orderByDesc('product_modal_prices.berlaku_mulai')
            ->select('product_modal_prices.*')
            ->paginate(12);

        $products = Product::where('tipe_akun', 'private')->orderBy('nama_akun')->get(['id', 'nama_akun']);

        return view('livewire.pages.admin.harga-modal.harga-modal-list', [
            'prices' => $prices,
            'products' => $products,
        ]);
    }
}
