<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public $nama_akun = '';

    public $tipe_akun = 'sharing';

    public $image;

    public $existingImage = null; // nama file lama di DB

    public $harga_awal = '';

    // Harga per durasi (fleksibel): [['durasi_value'=>1,'durasi_type'=>'bulan','harga'=>'20000'], ...]
    public $prices = [];

    public $deskripsi = '';

    public $mode = 'create';

    public function mount($product = null)
    {
        if ($product) {
            $this->product = $product;
            $this->nama_akun = $product->nama_akun;
            $this->tipe_akun = $product->tipe_akun ?: 'sharing';
            $this->image = null;
            $this->existingImage = $product->image;
            $this->harga_awal = $product->harga_awal;
            $this->deskripsi = $product->deskripsi;
            $this->mode = 'edit';

            $this->prices = $product->daftarHarga()->map(fn ($d) => [
                'durasi_value' => $d['durasi_value'],
                'durasi_type' => $d['durasi_type'],
                'harga' => (string) $d['harga'],
            ])->all();
        }

        if (empty($this->prices)) {
            $this->prices = [['durasi_value' => 1, 'durasi_type' => 'bulan', 'harga' => '']];
        }
    }

    public function addPrice()
    {
        $this->prices[] = ['durasi_value' => 1, 'durasi_type' => 'bulan', 'harga' => ''];
    }

    public function removePrice($index)
    {
        unset($this->prices[$index]);
        $this->prices = array_values($this->prices);
        if (empty($this->prices)) {
            $this->addPrice();
        }
    }

    private function toNumber($value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }

    /**
     * Harga Awal opsional. Kolom bertipe decimal, jadi string kosong harus jadi
     * null (bukan '') agar tidak ditolak DB ("Incorrect decimal value: ''").
     */
    private function normalizeHargaAwal(): ?int
    {
        if ($this->harga_awal === '' || $this->harga_awal === null) {
            return null;
        }

        return $this->toNumber($this->harga_awal);
    }

    public function save()
    {
        $rules = [
            'nama_akun' => 'required|min:3',
            'tipe_akun' => 'required|in:sharing,private',
            'harga_awal' => 'nullable|numeric',
            'deskripsi' => 'nullable|string',
            'prices' => 'required|array|min:1',
            'prices.*.durasi_value' => 'required|integer|min:1',
            'prices.*.durasi_type' => 'required|in:bulan,tahun',
            'prices.*.harga' => 'required',
        ];

        if ($this->mode === 'create') {
            $rules['image'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        } else {
            $rules['image'] = 'nullable|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules, [
            'prices.required' => 'Minimal 1 harga durasi harus diisi.',
            'prices.*.durasi_value.required' => 'Durasi harus diisi.',
            'prices.*.harga.required' => 'Harga harus diisi.',
        ]);

        // Cegah durasi ganda (kombinasi nilai + satuan)
        $keys = collect($this->prices)->map(fn ($p) => (int) $p['durasi_value'].'|'.$p['durasi_type']);
        if ($keys->count() !== $keys->unique()->count()) {
            $this->addError('prices', 'Terdapat durasi yang sama lebih dari sekali. Hapus duplikatnya.');

            return;
        }

        if ($this->mode === 'create') {
            $this->createProduct();
        } else {
            $this->updateProduct();
        }
    }

    private function createProduct()
    {
        try {
            $random = rand(10000, 99999);
            $filename = 'Product_'.$random.'.'.$this->image->getClientOriginalExtension();

            // simpan file fisik ke folder storage/app/public/img/banners
            $this->image->storeAs('img/Product', $filename, 'public');

            $product = Product::create([
                'nama_akun' => $this->nama_akun,
                'tipe_akun' => $this->tipe_akun,
                'image' => $filename,
                'harga_awal' => $this->normalizeHargaAwal(),
                'deskripsi' => $this->deskripsi,
            ]);
            $this->syncPrices($product);

            session()->flash('success', 'Product berhasil ditambahkan!');
            $this->dispatch('product-created');
            $this->resetForm();

            return redirect()->route('admin.product.index');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('product-save-error', message: 'Gagal menambahkan Product: '.$e->getMessage());
        }
    }

    private function updateProduct()
    {
        try {
            $data = [
                'nama_akun' => $this->nama_akun,
                'tipe_akun' => $this->tipe_akun,
                'harga_awal' => $this->normalizeHargaAwal(),
                'deskripsi' => $this->deskripsi,
            ];

            if ($this->image && is_object($this->image)) {
                if (! empty($this->existingImage) && Storage::disk('public')->exists('img/Product/'.$this->existingImage)) {
                    Storage::disk('public')->delete('img/Product/'.$this->existingImage);
                }

                $random = rand(10000, 99999);
                $filename = 'Product_'.$random.'.'.$this->image->getClientOriginalExtension();
                $this->image->storeAs('img/Product', $filename, 'public');

                $data['image'] = $filename;
            } else {
                $data['image'] = $this->existingImage;
            }

            $this->product->update($data);
            $this->syncPrices($this->product);

            session()->flash('success', 'Product berhasil diperbarui!');
            $this->dispatch('product-updated');
            $this->resetForm();

            return redirect()->route('admin.product.index');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('product-save-error', message: 'Gagal update Product: '.$e->getMessage());
        }
    }

    /**
     * Simpan harga per durasi ke tabel fleksibel + sinkron kolom lama (kompatibilitas publik).
     */
    private function syncPrices(Product $product): void
    {
        $product->prices()->delete();

        $legacy = [
            'harga_perbulan' => 0,
            'harga_5_perbulan' => 0,
            'harga_10_perbulan' => 0,
            'harga_pertahun' => 0,
        ];

        foreach ($this->prices as $row) {
            $v = (int) $row['durasi_value'];
            $t = $row['durasi_type'];
            $h = $this->toNumber($row['harga'] ?? 0);
            if ($v < 1) {
                continue;
            }

            $product->prices()->create([
                'durasi_value' => $v,
                'durasi_type' => $t,
                'harga' => $h,
            ]);

            if ($t === 'bulan' && $v === 1) {
                $legacy['harga_perbulan'] = $h;
            } elseif ($t === 'bulan' && $v === 5) {
                $legacy['harga_5_perbulan'] = $h;
            } elseif ($t === 'bulan' && $v === 10) {
                $legacy['harga_10_perbulan'] = $h;
            } elseif ($t === 'tahun' && $v === 1) {
                $legacy['harga_pertahun'] = $h;
            }
        }

        $product->update($legacy);
    }

    private function resetForm()
    {
        $this->nama_akun = '';
        $this->image = null;
        $this->harga_awal = '';
        $this->prices = [['durasi_value' => 1, 'durasi_type' => 'bulan', 'harga' => '']];
        $this->deskripsi = '';
    }

    public function render()
    {

        return view('livewire.pages.admin.product.product-form');
    }
}
