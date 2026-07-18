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

    // Produk JASA: customer wajib mengunggah dokumen (mis. cek plagiasi).
    public $butuh_file = false;

    // Mode jual jasa: 'paket' (per jumlah pengecekan) | 'halaman' (per halaman dokumen).
    public $jasa_mode = 'paket';

    // Mode add-on: 'multi' (boleh pilih beberapa) | 'tunggal' (pilih salah satu).
    public $addon_mode = 'multi';

    // Harga per halaman (dipakai bila jasa_mode = 'halaman').
    public $harga_per_halaman = '';

    // Add-on dinamis: [['nama'=>..,'keterangan'=>..,'harga'=>..], ...]
    public $addons = [];

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
            $this->butuh_file = (bool) $product->butuh_file;
            $this->jasa_mode = $product->jasa_mode ?: 'paket';
            $this->addon_mode = $product->addon_mode ?: 'multi';
            $this->harga_per_halaman = (string) ($product->hargaPerHalaman() ?: '');
            $this->addons = $product->addons()->orderBy('urutan')->get()
                ->map(fn ($a) => [
                    'nama' => $a->nama,
                    'keterangan' => $a->keterangan,
                    'harga' => (string) $a->harga,
                ])->all();
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
        // Jasa: paket per jumlah pengecekan (durasi_type 'kali'); akun: durasi 'bulan'.
        $this->prices[] = ['durasi_value' => 1, 'durasi_type' => $this->butuh_file ? 'kali' : 'bulan', 'harga' => ''];
    }

    /** Tambah baris add-on kosong. */
    public function addAddon()
    {
        $this->addons[] = ['nama' => '', 'keterangan' => '', 'harga' => ''];
    }

    public function removeAddon($index)
    {
        unset($this->addons[$index]);
        $this->addons = array_values($this->addons);
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
        // Buang baris add-on yang benar-benar kosong (tak dianggap diisi).
        $this->addons = array_values(array_filter(
            $this->addons,
            fn ($a) => trim((string) ($a['nama'] ?? '')) !== '' || trim((string) ($a['harga'] ?? '')) !== ''
        ));

        if ($this->butuh_file) {
            if ($this->jasa_mode === 'halaman') {
                // Dijual PER HALAMAN: harga disimpan sebagai satu baris (1 halaman).
                $this->prices = [[
                    'durasi_value' => 1,
                    'durasi_type' => 'halaman',
                    'harga' => $this->harga_per_halaman,
                ]];
            } else {
                // Paket per JUMLAH PENGECEKAN (1x, 5x, ...): durasi_type 'kali'.
                foreach ($this->prices as $i => $row) {
                    $this->prices[$i]['durasi_type'] = 'kali';
                }
            }
        }

        $rules = [
            'nama_akun' => 'required|min:3',
            'tipe_akun' => 'required|in:sharing,private',
            'harga_awal' => 'nullable|numeric',
            'deskripsi' => 'nullable|string',
            'prices' => 'required|array|min:1',
            'prices.*.durasi_value' => 'required|integer|min:1',
            'prices.*.durasi_type' => 'required|in:bulan,tahun,sekali,kali,halaman',
            'prices.*.harga' => 'required',
            'addons.*.nama' => 'required|string|max:100',
            'addons.*.harga' => 'required|numeric|min:0',
            'addons.*.keterangan' => 'nullable|string|max:150',
        ];

        // Jasa per halaman: harga per halaman wajib.
        if ($this->butuh_file && $this->jasa_mode === 'halaman') {
            $rules['harga_per_halaman'] = 'required|numeric|min:1';
        }

        if ($this->mode === 'create') {
            $rules['image'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        } else {
            $rules['image'] = 'nullable|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules, [
            'prices.required' => 'Minimal 1 harga durasi harus diisi.',
            'prices.*.durasi_value.required' => 'Durasi harus diisi.',
            'prices.*.harga.required' => 'Harga harus diisi.',
            'harga_per_halaman.required' => 'Harga per halaman harus diisi.',
            'addons.*.nama.required' => 'Nama add-on harus diisi.',
            'addons.*.harga.required' => 'Harga add-on harus diisi.',
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
                'butuh_file' => (bool) $this->butuh_file,
                'jasa_mode' => $this->butuh_file ? $this->jasa_mode : 'paket',
                'addon_mode' => $this->addon_mode,
                'image' => $filename,
                'harga_awal' => $this->normalizeHargaAwal(),
                'deskripsi' => $this->deskripsi,
            ]);
            $this->syncPrices($product);
            $this->syncAddons($product);

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
                'butuh_file' => (bool) $this->butuh_file,
                'jasa_mode' => $this->butuh_file ? $this->jasa_mode : 'paket',
                'addon_mode' => $this->addon_mode,
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
            $this->syncAddons($this->product);

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

    /**
     * Simpan ulang daftar add-on produk (hapus lalu buat ulang, seperti syncPrices).
     * Produk non-jasa tak pernah punya add-on, jadi tabelnya cukup dikosongkan.
     */
    private function syncAddons(Product $product): void
    {
        $product->addons()->delete();

        if (! $this->butuh_file) {
            return;
        }

        foreach (array_values($this->addons) as $i => $row) {
            $nama = trim((string) ($row['nama'] ?? ''));
            if ($nama === '') {
                continue;
            }

            $product->addons()->create([
                'nama' => $nama,
                'keterangan' => trim((string) ($row['keterangan'] ?? '')) ?: null,
                'harga' => $this->toNumber($row['harga'] ?? 0),
                'urutan' => $i,
                'aktif' => true,
            ]);
        }
    }

    private function resetForm()
    {
        $this->nama_akun = '';
        $this->butuh_file = false;
        $this->jasa_mode = 'paket';
        $this->addon_mode = 'multi';
        $this->harga_per_halaman = '';
        $this->addons = [];
        $this->image = null;
        $this->harga_awal = '';
        $this->prices = [['durasi_value' => 1, 'durasi_type' => 'bulan', 'harga' => '']];
        $this->deskripsi = '';
    }

    public function render()
    {
        // Daftar produk untuk picker nama add-on — supaya penamaan seragam
        // dengan nama produk yang sudah ada (mis. "cek plagiasi turnitin").
        $daftarProduk = Product::when($this->product, fn ($q) => $q->whereKeyNot($this->product->id))
            ->orderBy('nama_akun')
            ->get(['id', 'nama_akun']);

        return view('livewire.pages.admin.product.product-form', [
            'daftarProduk' => $daftarProduk,
        ]);
    }
}
