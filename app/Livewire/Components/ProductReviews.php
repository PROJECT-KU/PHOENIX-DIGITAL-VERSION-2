<?php

namespace App\Livewire\Components;

use App\Models\ProductBundlings;
use App\Models\ProductReview;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProductReviews extends Component
{
    /** Ulasan yang tampil pertama kali, dan tambahan tiap "Tampilkan lagi". */
    public const PER_MUAT = 5;

    /**
     * Batas atas ulasan dalam satu halaman. $tampil adalah properti publik yang
     * bisa diubah dari peramban; tanpa batas, satu permintaan bisa meminta
     * seluruh tabel sekaligus.
     */
    public const TAMPIL_MAKS = 200;

    public const URUTAN = ['terbaru', 'tertinggi', 'terendah'];

    /**
     * Batas kiriman ulasan per alamat IP, dalam jendela satu jam.
     *
     * Dua lapis: per target (satu produk/paket) dan TOTAL untuk semua target.
     * Tanpa batas total, satu perangkat bisa mengirim 3 ulasan ke setiap
     * produk dan paket — puluhan ulasan per jam yang menumpuk di antrean
     * moderasi admin.
     */
    public const BATAS_PER_TARGET = 3;

    public const BATAS_TOTAL = 10;

    public const JENDELA_DETIK = 3600;

    /**
     * Id target ulasan dan jenisnya ('produk' atau 'paket'). DIKUNCI: nilainya
     * datang dari halaman yang memasang komponen ini, bukan dari peramban —
     * tanpa kunci, ulasan bisa dialihkan ke produk atau paket lain.
     */
    #[Locked]
    public $productId;

    #[Locked]
    public string $jenis = ProductReview::JENIS_PRODUK;

    public $nama = '';

    public $rating = 5;

    public $ulasan = '';

    public bool $submitted = false;

    public int $tampil = self::PER_MUAT;

    /** Saring bintang 1–5, atau null untuk semua. */
    public ?int $bintang = null;

    public string $urut = 'terbaru';

    public function mount($productId, string $jenis = ProductReview::JENIS_PRODUK)
    {
        $this->productId = $productId;
        // Jenis yang tidak dikenal diperlakukan sebagai ulasan produk.
        $this->jenis = $jenis === ProductReview::JENIS_PAKET ? ProductReview::JENIS_PAKET : ProductReview::JENIS_PRODUK;
    }

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|min:2|max:60',
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'required|string|min:5|max:500',
        ];
    }

    public function submit()
    {
        $ip = request()->ip();
        // Format kunci per target sengaja tidak diubah, supaya hitungan yang
        // sedang berjalan saat kode ini dipasang tetap berlaku.
        $kunciTarget = 'product-review:'.$ip.':'.$this->jenis.':'.$this->productId;
        $kunciTotal = 'product-review-total:'.$ip;

        if (RateLimiter::tooManyAttempts($kunciTotal, self::BATAS_TOTAL)) {
            $this->addError('ulasan', 'Anda sudah mengirim '.self::BATAS_TOTAL.' ulasan dalam satu jam terakhir. '
                .'Coba lagi dalam '.$this->menitTunggu($kunciTotal).' menit.');

            return;
        }

        if (RateLimiter::tooManyAttempts($kunciTarget, self::BATAS_PER_TARGET)) {
            $sebutan = $this->jenis === ProductReview::JENIS_PAKET ? 'paket' : 'produk';
            $this->addError('ulasan', 'Terlalu banyak ulasan untuk '.$sebutan.' ini dari perangkat Anda. '
                .'Coba lagi dalam '.$this->menitTunggu($kunciTarget).' menit.');

            return;
        }

        $this->validate();

        // Paket bisa berakhir jadwalnya sementara halamannya masih terbuka;
        // ulasan untuk paket yang tak lagi dijual tidak diterima.
        if ($this->jenis === ProductReview::JENIS_PAKET && ! ProductBundlings::find($this->productId)?->sedangTayang()) {
            $this->addError('ulasan', 'Paket ini sudah tidak tersedia.');

            return;
        }

        // Dihitung SETELAH lolos validasi & cek paket: kiriman yang ditolak
        // tidak menghabiskan jatah.
        RateLimiter::hit($kunciTarget, self::JENDELA_DETIK);
        RateLimiter::hit($kunciTotal, self::JENDELA_DETIK);

        ProductReview::create([
            'product_id' => $this->productId,
            'jenis' => $this->jenis,
            'nama' => trim($this->nama),
            'rating' => (int) $this->rating,
            'ulasan' => trim($this->ulasan),
            'status' => 'pending', // menunggu persetujuan admin
        ]);

        $this->reset(['nama', 'ulasan']);
        $this->rating = 5;
        $this->submitted = true;
    }

    /** Sisa waktu tunggu sebuah kunci batas, dibulatkan ke atas, minimal 1 menit. */
    private function menitTunggu(string $kunci): int
    {
        return max(1, (int) ceil(RateLimiter::availableIn($kunci) / 60));
    }

    public function muatLagi(): void
    {
        $this->tampil = min($this->tampil + self::PER_MUAT, self::TAMPIL_MAKS);
    }

    /** Klik bintang yang sama sekali lagi melepas saringannya. */
    public function saringBintang($bintang = null): void
    {
        $b = (int) $bintang;
        $this->bintang = ($b >= 1 && $b <= 5 && $this->bintang !== $b) ? $b : null;
        $this->tampil = self::PER_MUAT;
    }

    public function updatedUrut(): void
    {
        $this->tampil = self::PER_MUAT;
    }

    public function render()
    {
        // Nilai dari peramban dirapikan dulu sebelum menyentuh query.
        if (! in_array($this->urut, self::URUTAN, true)) {
            $this->urut = 'terbaru';
        }
        if ($this->bintang !== null && ($this->bintang < 1 || $this->bintang > 5)) {
            $this->bintang = null;
        }
        $this->tampil = max(self::PER_MUAT, min($this->tampil, self::TAMPIL_MAKS));

        // Hanya ulasan milik target ini: halaman paket tidak menampilkan ulasan
        // produk isinya, dan sebaliknya.
        $base = ProductReview::approved()->untuk($this->jenis, $this->productId);

        // Skor, jumlah, dan sebaran selalu dari SEMUA ulasan — saringan hanya
        // mengubah daftar yang dibaca, bukan ringkasannya.
        $daftar = (clone $base)->when($this->bintang, fn ($q) => $q->where('rating', $this->bintang));

        match ($this->urut) {
            'tertinggi' => $daftar->orderByDesc('rating')->latest()->orderByDesc('id'),
            'terendah' => $daftar->orderBy('rating')->latest()->orderByDesc('id'),
            default => $daftar->latest()->orderByDesc('id'),
        };

        return view('livewire.components.product-reviews', [
            'reviews' => (clone $daftar)->take($this->tampil)->get(),
            'jumlahTersaring' => (clone $daftar)->reorder()->count(),
            'avg' => (clone $base)->avg('rating'),
            'count' => (clone $base)->count(),
            // Jumlah ulasan per bintang (5 => 12, 4 => 3, …) untuk batang sebaran.
            'sebaran' => (clone $base)->selectRaw('rating, count(*) as n')->groupBy('rating')->pluck('n', 'rating')->all(),
            'sebutan' => $this->jenis === ProductReview::JENIS_PAKET ? 'paket' : 'produk',
        ]);
    }
}
