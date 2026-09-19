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

    /** kartu | daftar — daftar memadatkan banyak ulasan dalam satu layar. */
    #[Url(as: 'tampilan', except: 'kartu')]
    public string $tampilan = 'kartu';

    /** Menampilkan arsip (yang sudah dihapus) alih-alih data aktif. */
    #[Url(as: 'arsip', except: false)]
    public bool $arsip = false;

    /** Jendela ringkasan per produk. */
    public bool $lihatRingkasan = false;

    /** Keputusan terakhir yang masih bisa diurungkan. */
    public ?array $urungkan = null;

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
        if (in_array($nama, ['fRating', 'fJenis', 'fDari', 'fSampai', 'urut', 'perHalaman', 'arsip'], true)) {
            $this->pilih = [];
            $this->resetPage();
        }

        if ($nama === 'urut' && ! in_array($this->urut, self::URUT, true)) {
            $this->urut = 'baru';
        }

        if ($nama === 'tampilan' && ! in_array($this->tampilan, ['kartu', 'daftar'], true)) {
            $this->tampilan = 'kartu';
        }
    }

    /** Izin memoderasi; memberi pesan yang jelas bila tidak punya. */
    protected function bolehModerasi(): bool
    {
        if (auth()->user()?->hasPermission('edit_productreview')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin memoderasi ulasan.');

        return false;
    }

    protected function bolehHapus(): bool
    {
        if (auth()->user()?->hasPermission('delete_productreview')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus ulasan.');

        return false;
    }

    /** Catatan siapa & kapan meninjau. */
    protected function jejakTinjau(array $tambahan = []): array
    {
        return array_merge(['ditinjau_at' => now(), 'ditinjau_oleh' => auth()->id()], $tambahan);
    }

    public function setFilter(string $f): void
    {
        $this->filter = in_array($f, self::STATUS, true) ? $f : 'pending';
        // Memilih tab status selalu keluar dari arsip — kalau tidak, tabnya
        // tampak berpindah tapi isinya tetap data terhapus.
        $this->arsip = false;
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
        if (! $this->bolehModerasi()) {
            return;
        }

        $u = ProductReview::find($id);
        if (! $u) {
            return;
        }

        $sebelum = $u->status;
        $u->update($this->jejakTinjau(['status' => 'approved']));
        $this->urungkan = ['id' => (string) $u->id, 'status' => $sebelum, 'nama' => $u->nama, 'aksi' => 'disetujui'];

        $this->dispatch('swal-success', message: 'Ulasan disetujui & kini tampil di halamannya.');
        // Sidebar komponen terpisah — beritahu agar badge langsung berkurang
        // tanpa perlu refresh halaman.
        $this->dispatch('sidebar-badge-updated');
    }

    public function reject($id): void
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $u = ProductReview::find($id);
        if (! $u) {
            return;
        }

        $sebelum = $u->status;
        $u->update($this->jejakTinjau(['status' => 'hidden']));
        $this->urungkan = ['id' => (string) $u->id, 'status' => $sebelum, 'nama' => $u->nama, 'aksi' => 'disembunyikan'];

        $this->dispatch('swal-success', message: 'Ulasan disembunyikan.');
        $this->dispatch('sidebar-badge-updated');
    }

    /** Kembalikan status ulasan ke sebelum keputusan terakhir. */
    public function urungkanTerakhir(): void
    {
        if (! $this->bolehModerasi() || ! $this->urungkan) {
            return;
        }

        $u = ProductReview::find($this->urungkan['id']);
        if (! $u) {
            $this->urungkan = null;

            return;
        }

        $semula = $this->urungkan['status'];
        $u->update([
            'status' => $semula,
            // Kembali menunggu berarti belum pernah ditinjau lagi.
            'ditinjau_at' => $semula === 'pending' ? null : now(),
            'ditinjau_oleh' => $semula === 'pending' ? null : auth()->id(),
        ]);

        $this->urungkan = null;
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Keputusan diurungkan.');
    }

    public function tutupUrungkan(): void
    {
        $this->urungkan = null;
    }

    /** Hapus = ARSIPKAN. Ulasan pembeli tidak hilang karena satu salah klik. */
    public function remove($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        ProductReview::whereKey($id)->delete();

        if ($this->lihatId === (string) $id) {
            $this->lihatId = null;
        }

        $this->dispatch('swal-success', message: 'Ulasan dipindahkan ke Arsip.');
        $this->dispatch('sidebar-badge-updated');
    }

    public function pulihkan(string $id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        ProductReview::onlyTrashed()->whereKey($id)->restore();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Ulasan dikembalikan dari arsip.');
    }

    public function buangPermanen(string $id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        ProductReview::onlyTrashed()->whereKey($id)->forceDelete();
        $this->lihatId = null;
        $this->dispatch('swal-success', message: 'Ulasan dibuang permanen.');
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
        if (! $this->bolehModerasi() || $this->pilih === []) {
            return;
        }

        $jumlah = ProductReview::whereKey($this->pilih)->update($this->jejakTinjau(['status' => $status]));
        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' ulasan '.$kata.'.');
    }

    /** Arsipkan yang dicentang (masih bisa dipulihkan). */
    public function hapusTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        $jumlah = ProductReview::whereKey($this->pilih)->delete();
        $this->pilih = [];
        $this->lihatId = null;
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' ulasan dipindahkan ke Arsip.');
    }

    public function pulihkanTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        $jumlah = ProductReview::onlyTrashed()->whereKey($this->pilih)->restore();
        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' ulasan dikembalikan dari arsip.');
    }

    public function buangTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        $jumlah = ProductReview::onlyTrashed()->whereKey($this->pilih)->forceDelete();
        $this->pilih = [];
        $this->lihatId = null;
        $this->dispatch('swal-success', message: $jumlah.' ulasan dibuang permanen.');
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
            ? ProductReview::withTrashed()->whereKey($this->pilih)
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
            ->when($this->arsip, fn ($q) => $q->onlyTrashed())
            // Di arsip, tab status TIDAK ikut menyaring: isinya campur semua
            // status, jadi menyaring lagi membuat arsip tampak kosong.
            ->when(! $this->arsip && $this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
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

    /**
     * Rekap per produk/paket dari ulasan yang DISETUJUI: jumlah & rata-rata,
     * diurutkan dari bintang terendah supaya yang bermasalah muncul lebih dulu.
     */
    protected function ringkasanPerProduk()
    {
        return ProductReview::approved()
            ->selectRaw('jenis, product_id, count(*) as jumlah, avg(rating) as rata')
            ->groupBy('jenis', 'product_id')
            ->orderBy('rata')
            ->orderByDesc('jumlah')
            ->limit(12)
            ->get()
            ->map(function ($baris) {
                // namaTarget() butuh relasi; instans ringan ini hanya membawa
                // jenis & product_id, jadi relasinya dimuat seperlunya.
                $contoh = ProductReview::with(['product', 'paket'])
                    ->where('jenis', $baris->jenis)->where('product_id', $baris->product_id)->first();

                return [
                    'nama' => $contoh?->namaTarget() ?? '—',
                    'jenis' => $baris->jenis,
                    'jumlah' => (int) $baris->jumlah,
                    'rata' => round((float) $baris->rata, 1),
                    'tautan' => $contoh?->tautanPublik(),
                ];
            });
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
            'arsip' => ProductReview::onlyTrashed()->count(),
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
            'detail' => $this->lihatId
                ? ProductReview::withTrashed()->with(['product', 'paket', 'peninjau', 'customer'])->find($this->lihatId)
                : null,
            // Produk mana yang paling banyak diulas & paling rendah bintangnya —
            // sinyal yang perlu ditindaklanjuti, bukan sekadar daftar ulasan.
            'ringkasanProduk' => $this->lihatRingkasan ? $this->ringkasanPerProduk() : collect(),
        ])->layout('livewire.layout.templateindex');
    }
}
