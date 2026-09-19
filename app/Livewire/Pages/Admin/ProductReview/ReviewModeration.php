<?php

namespace App\Livewire\Pages\Admin\ProductReview;

use App\Exports\UlasanProdukExport;
use App\Models\ProductReview;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ReviewModeration extends Component
{
    use WithPagination;

    #[Url(as: 'status', except: 'pending')]
    public string $filter = 'pending';

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    // ===== Saringan lanjutan =====
    #[Url(as: 'bintang', except: '')]
    public string $fRating = '';

    /** '' | produk | paket */
    #[Url(as: 'jenis', except: '')]
    public string $fJenis = '';

    #[Url(as: 'dari', except: '')]
    public string $fDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $fSampai = '';

    /** baru | lama | tinggi | rendah */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    #[Url(as: 'per', except: 12)]
    public int $perHalaman = 12;

    /** Ulasan yang sedang dibuka di jendela detail. */
    public ?string $lihatId = null;

    /** Id ulasan yang dicentang untuk aksi massal. */
    public array $pilih = [];

    public const URUT = ['baru', 'lama', 'tinggi', 'rendah'];

    public const STATUS = ['pending', 'approved', 'hidden', 'all'];

    public function updatedSearch(): void
    {
        $this->pilih = [];
        $this->resetPage();
    }

    public function updated($nama): void
    {
        if (in_array($nama, ['fRating', 'fJenis', 'fDari', 'fSampai', 'urut', 'perHalaman'], true)) {
            $this->pilih = [];
            $this->resetPage();
        }

        if ($nama === 'urut' && ! in_array($this->urut, self::URUT, true)) {
            $this->urut = 'baru';
        }
    }

    public function setFilter(string $f): void
    {
        $this->filter = in_array($f, self::STATUS, true) ? $f : 'pending';
        $this->pilih = [];
        $this->resetPage();
    }

    public function resetSaring(): void
    {
        $this->reset(['fRating', 'fJenis', 'fDari', 'fSampai', 'search', 'pilih']);
        $this->resetPage();
    }

    public function lepasSaring(string $nama): void
    {
        if (in_array($nama, ['fRating', 'fJenis', 'fDari', 'fSampai', 'search'], true)) {
            $this->$nama = '';
            $this->pilih = [];
            $this->resetPage();
        }
    }

    public function getAdaSaringProperty(): bool
    {
        return filled($this->fRating) || filled($this->fJenis) || filled($this->fDari) || filled($this->fSampai);
    }

    /** Saringan aktif sebagai chip yang bisa dilepas satu-satu. */
    public function getChipSaringProperty(): array
    {
        $chip = [];
        $tambah = function ($nama, $label) use (&$chip) {
            $chip[] = ['nama' => $nama, 'label' => $label];
        };

        if ($this->search !== '') {
            $tambah('search', 'Cari: "'.$this->search.'"');
        }
        if ($this->fRating !== '') {
            $tambah('fRating', $this->fRating.' bintang');
        }
        if ($this->fJenis !== '') {
            $tambah('fJenis', $this->fJenis === 'paket' ? 'Paket bundling' : 'Produk satuan');
        }
        if ($this->fDari !== '') {
            $tambah('fDari', 'Dari '.$this->fDari);
        }
        if ($this->fSampai !== '') {
            $tambah('fSampai', 'Sampai '.$this->fSampai);
        }

        return $chip;
    }

    // ===== Jendela detail =====

    public function lihat(string $id): void
    {
        $this->lihatId = ProductReview::whereKey($id)->value('id');
    }

    public function tutupLihat(): void
    {
        $this->lihatId = null;
    }

    /** Pindah ke tetangga dalam daftar yang SEDANG tampil. */
    public function detailTetangga(int $langkah): void
    {
        if (! $this->lihatId) {
            return;
        }

        $ids = $this->kueri()->pluck('id')->values();
        $i = $ids->search($this->lihatId);
        if ($i === false) {
            return;
        }

        if ($tujuan = $ids->get($i + $langkah)) {
            $this->lihatId = $tujuan;
        }
    }

    // ===== Moderasi =====

    public function approve($id): void
    {
        ProductReview::whereKey($id)->update(['status' => 'approved']);
        $this->dispatch('swal-success', message: 'Ulasan disetujui & kini tampil di halamannya.');
        // Sidebar komponen terpisah — beritahu agar badge langsung berkurang
        // tanpa perlu refresh halaman.
        $this->dispatch('sidebar-badge-updated');
    }

    public function reject($id): void
    {
        ProductReview::whereKey($id)->update(['status' => 'hidden']);
        $this->dispatch('swal-success', message: 'Ulasan disembunyikan.');
        $this->dispatch('sidebar-badge-updated');
    }

    public function remove($id): void
    {
        ProductReview::whereKey($id)->delete();

        if ($this->lihatId === (string) $id) {
            $this->lihatId = null;
        }

        $this->dispatch('swal-success', message: 'Ulasan dihapus permanen.');
        $this->dispatch('sidebar-badge-updated');
    }

    // ===== Aksi massal =====

    /** Centang/lepas semua yang tampil di halaman ini. */
    public function pilihHalaman(array $ids): void
    {
        $this->pilih = array_values(array_diff($ids, $this->pilih)) === []
            ? array_values(array_diff($this->pilih, $ids))
            : array_values(array_unique(array_merge($this->pilih, $ids)));
    }

    public function lepasPilih(): void
    {
        $this->pilih = [];
    }

    public function setujuiTerpilih(): void
    {
        $this->massal('approved', 'disetujui');
    }

    public function sembunyikanTerpilih(): void
    {
        $this->massal('hidden', 'disembunyikan');
    }

    protected function massal(string $status, string $kata): void
    {
        if ($this->pilih === []) {
            return;
        }

        $jumlah = ProductReview::whereKey($this->pilih)->update(['status' => $status]);
        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' ulasan '.$kata.'.');
    }

    public function hapusTerpilih(): void
    {
        if ($this->pilih === []) {
            return;
        }

        $jumlah = ProductReview::whereKey($this->pilih)->delete();
        $this->pilih = [];
        $this->lihatId = null;
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' ulasan dihapus permanen.');
    }

    // ===== Unduhan =====

    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('view_productreview'), 403);

        return Excel::download(new UlasanProdukExport($this->dataEkspor()), 'ulasan-produk-'.now()->format('Ymd-His').'.xlsx');
    }

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_productreview'), 403);

        $data = $this->dataEkspor();

        $pdf = Pdf::loadView('exports.ulasan-produk-pdf', [
            'ulasan' => $data,
            'saringan' => $this->pilih
                ? ['hanya '.count($this->pilih).' ulasan terpilih']
                : collect($this->chipSaring)->pluck('label')->all(),
            'rata' => round((float) $data->avg('rating'), 1),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), 'ulasan-produk-'.now()->format('Ymd-His').'.pdf');
    }

    /** Yang DICENTANG bila ada, kalau tidak ikut saringan yang sedang tampil. */
    protected function dataEkspor()
    {
        $kueri = $this->pilih
            ? ProductReview::whereKey($this->pilih)
            : $this->kueri();

        return $kueri->with(['product', 'paket'])->get();
    }

    /** Kueri daftar — dipakai tabel, ekspor, dan navigasi jendela detail. */
    protected function kueri()
    {
        $urutan = match ($this->urut) {
            'lama' => ['created_at', 'asc'],
            'tinggi' => ['rating', 'desc'],
            'rendah' => ['rating', 'asc'],
            default => ['created_at', 'desc'],
        };

        return ProductReview::query()
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->when($this->fRating !== '', fn ($q) => $q->where('rating', (int) $this->fRating))
            ->when($this->fJenis !== '', fn ($q) => $q->where('jenis', $this->fJenis))
            ->when($this->fDari !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->fDari))
            ->when($this->fSampai !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->fSampai))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('ulasan', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p->where('nama_akun', 'like', $term))
                        ->orWhereHas('paket', fn ($p) => $p->where('nama_paket', 'like', $term));
                });
            })
            ->orderBy($urutan[0], $urutan[1]);
    }

    public function render()
    {
        // Ulasan bisa untuk produk atau paket; keduanya dimuat sekaligus.
        $reviews = $this->kueri()
            ->with(['product', 'paket'])
            ->paginate(max(6, min(48, $this->perHalaman)));

        // Satu kueri untuk semua hitungan tab.
        $perStatus = ProductReview::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $tabCounts = [
            'all' => (int) $perStatus->sum(),
            'pending' => (int) ($perStatus['pending'] ?? 0),
            'approved' => (int) ($perStatus['approved'] ?? 0),
            'hidden' => (int) ($perStatus['hidden'] ?? 0),
        ];

        // Sebaran bintang dari ulasan yang TAMPIL di halaman produk.
        $sebaran = ProductReview::approved()
            ->selectRaw('rating, count(*) as jumlah')
            ->groupBy('rating')->pluck('jumlah', 'rating');
        $jumlahDisetujui = (int) $sebaran->sum();
        $totalSebaran = max(1, $jumlahDisetujui);

        return view('livewire.pages.admin.product-review.review-moderation', [
            'reviews' => $reviews,
            'tabCounts' => $tabCounts,
            'rataRating' => $jumlahDisetujui
                ? round($sebaran->reduce(fn ($t, $n, $b) => $t + ($b * $n), 0) / $jumlahDisetujui, 1)
                : 0.0,
            'sebaran' => collect(range(5, 1))->mapWithKeys(fn ($b) => [$b => [
                'jumlah' => (int) ($sebaran[$b] ?? 0),
                'persen' => round(((int) ($sebaran[$b] ?? 0)) / $totalSebaran * 100),
            ]]),
            'detail' => $this->lihatId ? ProductReview::with(['product', 'paket'])->find($this->lihatId) : null,
        ])->layout('livewire.layout.templateindex');
    }
}
