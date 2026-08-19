<?php

namespace App\Livewire\Pages\Public\Bundling;

use App\Livewire\Concerns\MengirimPixel;
use App\Models\ProductBundlings as ModelsProductBundlings;
use App\Support\HargaPaket;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman detail satu paket bundling, sejajar dengan halaman detail produk
 * satuan di shop: alamatnya sendiri, bisa dibagikan, dan terbaca mesin pencari.
 */
class Detail extends Component
{
    use MengirimPixel;

    public ModelsProductBundlings $paket;

    public function mount($id)
    {
        $paket = ModelsProductBundlings::with(['product1', 'product2', 'product3', 'product4', 'product5'])
            ->find($id);

        // Paket berjadwal bisa berakhir sementara tautannya masih beredar;
        // halamannya ikut hilang, bukan menampilkan paket yang tak bisa dibeli.
        if (! $paket || ! $paket->sedangTayang()) {
            abort(404);
        }

        $this->paket = $paket;
        $this->shareSeo();
    }

    /** Daftar produk di dalam paket beserta durasinya. */
    public function isiPaket(): array
    {
        $durs = $this->paket->durations ?? [];
        $isi = [];

        foreach ([1, 2, 3, 4, 5] as $i) {
            $p = $this->paket->{'product'.$i};
            if (! $p) {
                continue;
            }
            $dur = $durs['product_'.$i] ?? null;
            $isi[] = [
                'nama' => $p->nama_akun,
                'dur_value' => (int) ($dur['value'] ?? 1),
                'dur_type' => ucfirst($dur['type'] ?? 'bulan'),
            ];
        }

        return $isi;
    }

    /** Paket lain yang masih tayang, sebagai saran. */
    public function paketLain()
    {
        return ModelsProductBundlings::tayang()
            ->where('id', '!=', $this->paket->id)
            ->latest()
            ->take(4)
            ->get();
    }

    public function addToCart()
    {
        $paket = $this->paket->fresh();

        // Jadwal diperiksa ulang saat tombol ditekan: halaman bisa dibuka lama
        // dan paketnya keburu berakhir.
        if (! $paket || ! $paket->sedangTayang()) {
            $this->dispatch('cart-error', message: 'Paket sudah tidak tersedia.');

            return;
        }

        $cart = session()->get('cart', []);
        $kunci = "bundling_{$paket->id}";
        $harga = (int) preg_replace('/[^0-9]/', '', (string) $paket->harga_bundling);

        // Akun digital: 1 baris = 1 item, tidak menumpuk jumlah.
        $cart[$kunci] = [
            'product_id' => $paket->id,
            'product_name' => $paket->nama_paket,
            'product_image' => $paket->gambar ? basename($paket->gambar) : null,
            'duration_type' => null,
            'duration_value' => null,
            // Harga coret, dibawa serta supaya PromoService tidak perlu membaca
            // database tiap kali menghitung (lihat PromoService::hargaAwalPaket).
            'harga_awal' => (int) preg_replace('/[^0-9]/', '', (string) $paket->harga_awal),
            'type' => 'bundling',
            'price' => $harga,
            'quantity' => 1,
            'subtotal' => $harga,
        ];

        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: count($cart));
        $this->kirimPixel('AddToCart', $this->pixelDariBarisKeranjang($cart[$kunci]));
        $this->dispatch('cart-success', message: 'Paket berhasil ditambahkan ke keranjang!');
    }

    private function shareSeo(): void
    {
        $p = $this->paket;
        $nama = trim(preg_replace('/\s+/', ' ', (string) $p->nama_paket));
        $desc = $p->deskripsi
            ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($p->deskripsi))), 155)
            : 'Paket '.$nama.' di Phoenix Digital — beberapa akun premium sekaligus, lebih hemat dan bergaransi.';
        $gambar = $p->gambar
            ? asset('storage/img/ProductBundlings/'.basename($p->gambar))
            : asset(config('seo.image'));

        view()->share('seoTitle', $nama.' — Paket Akun Premium Hemat | Phoenix Digital');
        view()->share('seoDescription', $desc);
        view()->share('seoCrumbName', $nama);
        view()->share('seoKeywords', $nama.', paket '.$nama.', bundling akun premium, paket hemat akun premium');
        if ($p->gambar) {
            view()->share('seoImage', 'storage/img/ProductBundlings/'.basename($p->gambar));
        }
        view()->share('seoJsonLd', json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $nama,
            'description' => $desc,
            'image' => $gambar,
            'brand' => ['@type' => 'Brand', 'name' => 'Phoenix Digital'],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => HargaPaket::untuk($p)['bayar'],
                'availability' => 'https://schema.org/InStock',
                'url' => url()->current(),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.bundling.detail', [
            'hp' => HargaPaket::untuk($this->paket),
            'isi' => $this->isiPaket(),
            'lainnya' => $this->paketLain(),
        ]);
    }
}
