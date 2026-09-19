<?php

namespace App\Livewire\Pages\Public\Testimoni;

use App\Models\Testimoni;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman /testimoni — semua testimoni yang disetujui.
 *
 * Beranda hanya memuat beberapa kartu (lihat Testimoni::jumlahBeranda), jadi
 * testimoni bagus yang lebih lama tidak pernah terbaca pengunjung. Halaman ini
 * memakai saringan yang PERSIS sama dengan beranda (scopeTampilPublik), supaya
 * tidak ada testimoni yang muncul di sini tapi sengaja disembunyikan di sana.
 */
class SemuaTestimoni extends Component
{
    use WithPagination;

    /** '' | 5 | 4 | 3 | 2 | 1 — saringan bintang untuk pengunjung. */
    #[Url(as: 'bintang', except: '')]
    public string $bintang = '';

    #[Url(as: 'cari', except: '')]
    public string $cari = '';

    /** pilihan | baru | tinggi — 'pilihan' mengikuti urutan yang diatur admin. */
    #[Url(as: 'urut', except: 'pilihan')]
    public string $urut = 'pilihan';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedUrut(): void
    {
        if (! in_array($this->urut, ['pilihan', 'baru', 'tinggi'], true)) {
            $this->urut = 'pilihan';
        }
        $this->resetPage();
    }

    public function setBintang(string $nilai): void
    {
        $this->bintang = in_array($nilai, ['5', '4', '3', '2', '1'], true) ? $nilai : '';
        $this->resetPage();
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $dasar = fn () => Testimoni::tampilPublik();

        // Sebaran dihitung SEKALI, lalu dipakai untuk total, rata-rata, chip
        // saringan, dan data terstruktur — dulu count & avg dijalankan dua kali.
        $sebaran = $dasar()->selectRaw('rating, count(*) as jumlah')->groupBy('rating')->pluck('jumlah', 'rating');
        $total = (int) $sebaran->sum();
        $rata = $total ? round($sebaran->reduce(fn ($t, $n, $b) => $t + ($b * $n), 0) / $total, 1) : 0.0;

        $this->bagikanSeo($total, $rata);

        $testimoni = $dasar()
            ->when($this->bintang !== '', fn ($q) => $q->where('rating', (int) $this->bintang))
            ->when($this->cari !== '', fn ($q) => $q->where(fn ($s) => $s
                ->where('pesan', 'like', '%'.$this->cari.'%')
                ->orWhere('peran', 'like', '%'.$this->cari.'%')))
            ->with(['customer' => fn ($q) => $q->withCount([
                'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
            ])])
            ->when($this->urut === 'baru', fn ($q) => $q->orderByDesc('created_at'))
            ->when($this->urut === 'tinggi', fn ($q) => $q->orderByDesc('rating')->orderByDesc('created_at'))
            ->when($this->urut === 'pilihan', fn ($q) => $q->urutTampil())
            ->paginate(12);

        return view('livewire.pages.public.testimoni.semua', [
            'testimoni' => $testimoni,
            'total' => $total,
            'rata' => $rata,
            'sebaran' => collect(range(5, 1))->mapWithKeys(fn ($b) => [$b => (int) ($sebaran[$b] ?? 0)]),
        ]);
    }

    /**
     * Data terstruktur: rata-rata bintang + beberapa ulasan teratas.
     *
     * Tanpa ini angka 4,9 tidak pernah sampai ke mesin pencari, jadi bintang
     * kuning di hasil pencarian tidak pernah muncul. Nama yang dikirim WAJIB
     * nama_publik — pengirim anonim tidak boleh bocor ke pihak ketiga.
     */
    protected function bagikanSeo(int $jumlah, float $rata): void
    {
        if ($jumlah < 1) {
            return;
        }

        $contoh = Testimoni::tampilPublik()->urutTampil()->take(5)->get();

        view()->share('seoTitle', 'Testimoni Pelanggan Phoenix Digital');
        view()->share('seoDescription', $jumlah.' testimoni pelanggan Phoenix Digital, rata-rata '.number_format($rata, 1, ',', '.').' dari 5 bintang. Semuanya ditinjau admin sebelum tampil.');
        view()->share('seoJsonLd', json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => config('seo.site_name', 'Phoenix Digital'),
            'url' => url('/'),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $rata,
                'reviewCount' => $jumlah,
                'bestRating' => 5,
                'worstRating' => 1,
            ],
            'review' => $contoh->map(fn ($t) => [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => $t->nama_publik],
                'datePublished' => $t->created_at?->toDateString(),
                'reviewBody' => $t->pesan,
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (int) $t->rating,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ],
            ])->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
