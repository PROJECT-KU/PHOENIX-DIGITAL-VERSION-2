<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\JasaDraftUpload;
use App\Models\Product;
use App\Services\PromoService;
use App\Support\PdfPageCounter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductDetail extends Component
{
    use WithFileUploads;

    public $product;

    public int $quantity = 1;

    // ===== Khusus produk JASA =====
    /** ID add-on yang dipilih customer. */
    public array $selectedAddons = [];

    /** Dokumen PDF untuk jasa per halaman (parafrase) — diunggah SEBELUM bayar. */
    public $dokumenJasa;

    /** Draft upload tersimpan (dipindahkan ke pesanan saat checkout). */
    public ?string $draftUploadId = null;

    public ?string $draftNamaFile = null;

    /** Jumlah halaman hasil hitung sistem (0 = belum ada file). */
    public int $jumlahHalaman = 0;

    /** Halaman yang tak perlu dikerjakan, mis. "1,2,12" atau "1-3,12". */
    public string $halamanDikecualikan = '';

    public ?string $durationType = null;

    public ?int $durationValue = null;

    public int $pickCustomMonths = 3;

    public bool $isCustom = false;

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);

        // Pilih paket pertama sebagai default
        $rows = $this->product->daftarHarga();
        if ($rows->isNotEmpty()) {
            $this->durationType = $rows->first()['durasi_type'];
            $this->durationValue = (int) $rows->first()['durasi_value'];
        }

        $this->shareSeo();
    }

    /**
     * SEO khusus halaman produk: judul & deskripsi dari nama produk + JSON-LD Product.
     */
    private function shareSeo(): void
    {
        $p = $this->product;
        $name = trim(preg_replace('/\s+/', ' ', (string) $p->nama_akun));
        $desc = $p->deskripsi
            ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($p->deskripsi))), 155)
            : 'Beli '.$name.' di Phoenix Digital — akun premium bergaransi, proses cepat & aman.';
        $imgUrl = $p->image ? asset('storage/img/Product/'.basename($p->image)) : asset(config('seo.image'));

        view()->share('seoTitle', $name.' — Akun Premium Bergaransi | Phoenix Digital');
        view()->share('seoDescription', $desc);
        view()->share('seoCrumbName', $name);
        view()->share('seoKeywords', $name.', jual '.$name.', '.$name.' murah, '.$name.' bergaransi, akun premium murah, tools AI');
        if ($p->image) {
            view()->share('seoImage', 'storage/img/Product/'.basename($p->image));
        }
        view()->share('seoJsonLd', json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $p->nama_akun,
            'description' => $desc,
            'image' => $imgUrl,
            'brand' => ['@type' => 'Brand', 'name' => 'Phoenix Digital'],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => (int) ($p->harga_perbulan ?? 0),
                'availability' => 'https://schema.org/InStock',
                'url' => url()->current(),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Computed]
    public function bestDiscount()
    {
        return $this->promoService->getBestProductDiscount($this->product->id, null);
    }

    /** Produk lain untuk rekomendasi — diurut dari harga paling mirip (read-only). */
    #[Computed]
    public function relatedProducts()
    {
        $price = (int) ($this->product->harga_perbulan ?? 0);

        return Product::where('id', '!=', $this->product->id)
            ->orderByRaw('ABS(COALESCE(harga_perbulan, 0) - ?) asc', [$price])
            ->take(10)
            ->get();
    }

    public function applyDiscount(int $harga): int
    {
        $best = $this->bestDiscount;
        if (! $best || empty($best['value'])) {
            return $harga;
        }
        if (($best['type'] ?? '') === 'persen') {
            // floor pada nilai diskon — sama persis dengan PromoService (tanpa pembulatan)
            return (int) ($harga - floor($harga * $best['value'] / 100));
        }

        return (int) max(0, $harga - $best['value']);
    }

    public function selectedHarga(): int
    {
        if ($this->isCustom) {
            return $this->customPricing()['base'];
        }

        foreach ($this->product->daftarHarga() as $r) {
            if ($r['durasi_type'] === $this->durationType && (int) $r['durasi_value'] === (int) $this->durationValue) {
                return (int) $r['harga'];
            }
        }

        return (int) ($this->product->harga_awal ?? 0);
    }

    /**
     * Harga durasi custom. Bila jumlah bulan cocok dengan paket "bulan" yang sudah ada,
     * ikuti harga paket itu; selain itu = bulan × harga per bulan.
     */
    public function customPricing(): array
    {
        $months = (int) $this->pickCustomMonths;

        foreach ($this->product->daftarHarga() as $r) {
            if ($r['durasi_type'] === 'bulan' && (int) $r['durasi_value'] === $months) {
                $base = (int) $r['harga'];
                $disc = $this->applyDiscount($base);

                return ['base' => $base, 'discounted' => $disc, 'savings' => max(0, $base - $disc), 'matched' => true];
            }
        }

        // Hitung per bulan lalu dikali agar hemat konsisten (mis. 7.631 × 4 = 30.524),
        // tidak ada selisih akibat pembulatan pada total.
        $perBulan = (int) ($this->product->harga_perbulan ?? 0);
        $discPerBulan = $this->applyDiscount($perBulan);
        $base = $months * $perBulan;
        $disc = $months * $discPerBulan;

        return ['base' => $base, 'discounted' => $disc, 'savings' => max(0, $base - $disc), 'matched' => false];
    }

    /**
     * Pilih/lepas add-on. Bila produk ber-mode 'tunggal', memilih add-on lain
     * otomatis menggantikan pilihan sebelumnya (bertingkat, bukan menumpuk).
     */
    public function toggleAddon(string $addonId): void
    {
        if (in_array($addonId, $this->selectedAddons, true)) {
            $this->selectedAddons = array_values(array_diff($this->selectedAddons, [$addonId]));

            return;
        }

        $this->selectedAddons = $this->product->addonPilihSatu()
            ? [$addonId]
            : array_values(array_merge($this->selectedAddons, [$addonId]));
    }

    /** Total tambahan harga dari add-on terpilih. */
    #[Computed]
    public function addonsTotal(): int
    {
        return (int) $this->product->addonAktif()
            ->whereIn('id', $this->selectedAddons)
            ->sum('harga');
    }

    /** Rincian add-on terpilih (untuk disimpan ke keranjang/pesanan). */
    private function addonsTerpilih(): array
    {
        return $this->product->addonAktif()
            ->whereIn('id', $this->selectedAddons)
            ->map(fn ($a) => ['id' => $a->id, 'nama' => $a->nama, 'harga' => (int) $a->harga])
            ->values()->all();
    }

    /**
     * Customer mengunggah PDF untuk jasa per halaman. Sistem menghitung
     * halamannya agar harga bisa ditentukan SEBELUM pembayaran.
     * Hanya PDF: jumlah halaman DOCX tidak bisa dibaca dengan andal.
     */
    public function updatedDokumenJasa(): void
    {
        try {
            $this->validateOnly('dokumenJasa', [
                'dokumenJasa' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            ], [
                'dokumenJasa.mimes' => 'File harus PDF. Simpan dokumen Anda sebagai PDF terlebih dahulu.',
                'dokumenJasa.max' => 'Ukuran file maksimal 20 MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->reset('dokumenJasa');
            throw $e;
        }

        $halaman = PdfPageCounter::hitung($this->dokumenJasa->getRealPath());

        if (! $halaman) {
            $this->reset('dokumenJasa');
            $this->addError('dokumenJasa', 'Jumlah halaman tidak terbaca. Pastikan PDF tidak terkunci/hasil pindaian bergambar.');

            return;
        }

        // Simpan ke disk privat sebagai draft (dipindah ke pesanan saat checkout).
        $path = $this->dokumenJasa->store('jasa-draft', 'local');

        $draft = JasaDraftUpload::create([
            'product_id' => $this->product->id,
            'path' => $path,
            'nama_asli' => $this->dokumenJasa->getClientOriginalName(),
            'ukuran' => $this->dokumenJasa->getSize(),
            'mime' => $this->dokumenJasa->getMimeType(),
            'jumlah_halaman' => $halaman,
            'session_token' => session()->getId(),
        ]);

        $this->draftUploadId = $draft->id;
        $this->draftNamaFile = $draft->nama_asli;
        $this->jumlahHalaman = $halaman;
        $this->reset('dokumenJasa');
    }

    /** Buang file draft yang sudah diunggah (customer ingin ganti). */
    public function hapusDraft(): void
    {
        if ($this->draftUploadId) {
            optional(JasaDraftUpload::find($this->draftUploadId))->delete();
        }
        $this->draftUploadId = null;
        $this->draftNamaFile = null;
        $this->jumlahHalaman = 0;
        $this->halamanDikecualikan = '';
        unset($this->halamanExclude, $this->halamanDihitung, $this->hargaPerHalamanTotal);
    }

    /**
     * Ubah teks "1,2,5-8" jadi daftar nomor halaman yang sah (unik, urut,
     * dalam rentang 1..total). Nomor di luar rentang diabaikan diam-diam
     * agar customer tak terjebak error; ringkasan di layar tetap menunjukkan
     * berapa halaman yang benar-benar dikecualikan.
     *
     * @return array<int,int>
     */
    #[Computed]
    public function halamanExclude(): array
    {
        $total = max(0, $this->jumlahHalaman);
        if ($total < 1 || trim($this->halamanDikecualikan) === '') {
            return [];
        }

        $out = [];
        foreach (preg_split('/[^0-9\-]+/', $this->halamanDikecualikan, -1, PREG_SPLIT_NO_EMPTY) as $bagian) {
            if (preg_match('/^(\d+)-(\d+)$/', $bagian, $m)) {
                $a = (int) $m[1];
                $b = (int) $m[2];
                if ($a > $b) {
                    [$a, $b] = [$b, $a];
                }
                for ($i = $a; $i <= $b; $i++) {
                    $out[] = $i;
                }
            } elseif (ctype_digit($bagian)) {
                $out[] = (int) $bagian;
            }
        }

        $out = array_values(array_unique(array_filter($out, fn ($n) => $n >= 1 && $n <= $total)));
        sort($out);

        return $out;
    }

    /** Jumlah halaman yang benar-benar dikerjakan & ditagih. */
    #[Computed]
    public function halamanDihitung(): int
    {
        return max(0, $this->jumlahHalaman - count($this->halamanExclude()));
    }

    /** Tandai halaman pertama sebagai dikecualikan (pintasan). */
    public function tandaiHalamanPertama(): void
    {
        $this->tambahHalamanExclude(1);
    }

    /** Tandai halaman terakhir sebagai dikecualikan (pintasan). */
    public function tandaiHalamanTerakhir(): void
    {
        if ($this->jumlahHalaman > 0) {
            $this->tambahHalamanExclude($this->jumlahHalaman);
        }
    }

    private function tambahHalamanExclude(int $nomor): void
    {
        $daftar = $this->halamanExclude();
        if (! in_array($nomor, $daftar, true)) {
            $daftar[] = $nomor;
            sort($daftar);
        }
        $this->halamanDikecualikan = implode(',', $daftar);
        unset($this->halamanExclude, $this->halamanDihitung, $this->hargaPerHalamanTotal);
    }

    /** Kosongkan daftar halaman yang dikecualikan. */
    public function hapusHalamanExclude(): void
    {
        $this->halamanDikecualikan = '';
        unset($this->halamanExclude, $this->halamanDihitung, $this->hargaPerHalamanTotal);
    }

    /** Harga dasar jasa per halaman = harga/halaman × halaman yang DIKERJAKAN. */
    #[Computed]
    public function hargaPerHalamanTotal(): int
    {
        return $this->product->hargaPerHalaman() * $this->halamanDihitung();
    }

    public function selectPackage(string $type, int $value)
    {
        $this->isCustom = false;
        $this->durationType = $type;
        $this->durationValue = $value;
    }

    public function chooseCustom()
    {
        if ((int) ($this->product->harga_perbulan ?? 0) <= 0) {
            return;
        }
        $this->isCustom = true;
        $this->durationType = 'bulan';
        $this->durationValue = (int) $this->pickCustomMonths;
    }

    public function incCustom()
    {
        $this->pickCustomMonths = min(60, $this->pickCustomMonths + 1);
        $this->chooseCustom();
    }

    public function decCustom()
    {
        $this->pickCustomMonths = max(1, $this->pickCustomMonths - 1);
        $this->chooseCustom();
    }

    public function addToCart()
    {
        // ===== Jasa PER HALAMAN (mis. parafrase): file wajib diunggah dulu =====
        if ($this->product->jasaPerHalaman()) {
            return $this->addToCartPerHalaman();
        }

        if (! $this->durationType || ! $this->durationValue) {
            $this->dispatch('cart-error', message: 'Silakan pilih paket harga terlebih dahulu.');

            return;
        }

        $this->quantity = max(1, (int) $this->quantity);

        $price = $this->getPrice($this->product, $this->durationType, $this->durationValue);

        // Durasi custom (admin belum set harga) → bulan × harga per bulan
        if (! $price && $this->durationType === 'bulan') {
            $perBulan = (int) ($this->product->harga_perbulan ?? 0);
            if ($perBulan > 0 && $this->durationValue > 0) {
                $price = $perBulan * $this->durationValue;
            }
        }

        if (! $price) {
            $this->dispatch('cart-error', message: 'Paket tidak valid.');

            return;
        }

        // Add-on hanya berlaku untuk produk jasa; produk biasa selalu kosong.
        $addons = $this->product->butuh_file ? $this->addonsTerpilih() : [];
        $addonsTotal = (int) array_sum(array_column($addons, 'harga'));
        $hargaSatuan = $price + $addonsTotal;

        $cart = session()->get('cart', []);
        // Add-on masuk kunci agar pilihan berbeda tidak saling menimpa.
        $suffixAddon = $addons ? '_'.substr(md5(implode(',', array_column($addons, 'id'))), 0, 8) : '';
        $cartKey = "{$this->product->id}_{$this->durationType}_{$this->durationValue}{$suffixAddon}";
        $imageName = $this->product->image ? basename($this->product->image) : null;

        if (isset($cart[$cartKey])) {
            // Akun digital: 1 baris = 1 item, tidak menumpuk jumlah.
            $cart[$cartKey]['quantity'] = 1;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $this->product->id,
                'product_name' => $this->product->nama_akun,
                'product_image' => $imageName,
                'duration_type' => $this->durationType,
                'duration_value' => $this->durationValue,
                'price' => $hargaSatuan,
                'quantity' => $this->quantity,
                'subtotal' => $hargaSatuan * $this->quantity,
                'addons' => $addons,
                'addons_total' => $addonsTotal,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->dispatch('cart-success', message: 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Keranjang untuk jasa PER HALAMAN: harga = harga/halaman × jumlah halaman
     * (dari file yang sudah diunggah) + add-on. Satu pesanan = satu dokumen.
     */
    private function addToCartPerHalaman()
    {
        if (! $this->draftUploadId || $this->jumlahHalaman < 1) {
            $this->dispatch('cart-error', message: 'Unggah dokumen PDF dulu agar jumlah halaman & harganya bisa dihitung.');

            return;
        }

        $perHalaman = $this->product->hargaPerHalaman();
        if ($perHalaman <= 0) {
            $this->dispatch('cart-error', message: 'Harga layanan belum diatur. Silakan hubungi admin.');

            return;
        }

        $dihitung = $this->halamanDihitung();
        if ($dihitung < 1) {
            $this->dispatch('cart-error', message: 'Semua halaman dikecualikan. Sisakan minimal 1 halaman untuk dikerjakan.');

            return;
        }

        $addons = $this->addonsTerpilih();
        $addonsTotal = (int) array_sum(array_column($addons, 'harga'));
        $harga = $perHalaman * $dihitung + $addonsTotal;
        $excludeList = $this->halamanExclude();

        $cart = session()->get('cart', []);
        // Tiap dokumen = baris keranjang tersendiri (kunci pakai id draft).
        $cartKey = "{$this->product->id}_halaman_{$this->draftUploadId}";

        $cart[$cartKey] = [
            'product_id' => $this->product->id,
            'product_name' => $this->product->nama_akun,
            'product_image' => $this->product->image ? basename($this->product->image) : null,
            'duration_type' => 'halaman',
            'duration_value' => 1,          // 1 dokumen untuk dikerjakan
            'price' => $harga,
            'quantity' => 1,
            'subtotal' => $harga,
            'addons' => $addons,
            'addons_total' => $addonsTotal,
            'jumlah_halaman' => $this->jumlahHalaman,
            'halaman_dikecualikan' => $excludeList ? implode(',', $excludeList) : null,
            'halaman_dihitung' => $dihitung,
            'draft_upload_id' => $this->draftUploadId,
        ];

        session()->put('cart', $cart);

        // Reset agar customer bisa mengunggah dokumen berikutnya.
        $this->draftUploadId = null;
        $this->draftNamaFile = null;
        $this->jumlahHalaman = 0;
        $this->halamanDikecualikan = '';
        $this->selectedAddons = [];
        unset($this->halamanExclude, $this->halamanDihitung, $this->hargaPerHalamanTotal);

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->dispatch('cart-success', message: 'Dokumen ditambahkan ke keranjang!');
    }

    private function getPrice(Product $product, string $durationType, int $durationValue)
    {
        $harga = $product->hargaUntuk($durationValue, $durationType);

        return $harga > 0 ? $harga : null;
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);

        return count($cart);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.product-detail');
    }
}
