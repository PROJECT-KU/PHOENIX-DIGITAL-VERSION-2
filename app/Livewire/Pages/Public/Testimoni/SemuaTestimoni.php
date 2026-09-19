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

    public function setBintang(string $nilai): void
    {
        $this->bintang = in_array($nilai, ['5', '4', '3', '2', '1'], true) ? $nilai : '';
        $this->resetPage();
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $dasar = fn () => Testimoni::tampilPublik();

        $testimoni = $dasar()
            ->when($this->bintang !== '', fn ($q) => $q->where('rating', (int) $this->bintang))
            ->with(['customer' => fn ($q) => $q->withCount([
                'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
            ])])
            ->urutTampil()
            ->paginate(12);

        $sebaran = $dasar()->selectRaw('rating, count(*) as jumlah')->groupBy('rating')->pluck('jumlah', 'rating');

        return view('livewire.pages.public.testimoni.semua', [
            'testimoni' => $testimoni,
            'total' => (int) $sebaran->sum(),
            'rata' => round((float) $dasar()->avg('rating'), 1),
            'sebaran' => collect(range(5, 1))->mapWithKeys(fn ($b) => [$b => (int) ($sebaran[$b] ?? 0)]),
        ]);
    }
}
