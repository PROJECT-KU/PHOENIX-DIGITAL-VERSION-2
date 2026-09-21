<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Exports\ArtikelExport;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\ImporArtikel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class BlogList extends Component
{
    use WithFileUploads;
    use WithPagination;

    /** all | published | terjadwal | draft | sampah — tab yang sekaligus kartu hitungan. */
    #[Url(as: 'tab', except: 'all')]
    public string $filter = 'all';

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    #[Url(as: 'category', keep: false)]
    public string $category = '';

    #[Url(as: 'tag', keep: false)]
    public string $tag = '';

    /** baru | lama | populer | populer30 | judul | diperbarui */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    /** kartu | tabel */
    #[Url(as: 'tampilan', except: 'kartu')]
    public string $tampilan = 'kartu';

    #[Url(as: 'per', except: 12)]
    public int $perPage = 12;

    /** Hanya artikel yang disematkan. */
    #[Url(as: 'unggulan', except: false)]
    public bool $fUnggulan = false;

    /** Hanya artikel mandek (lama terbit, tak ada pembaca sebulan terakhir). */
    #[Url(as: 'mandek', except: false)]
    public bool $fMandek = false;

    /** Id artikel yang dicentang untuk aksi massal. */
    public array $pilih = [];

    /** Id artikel yang baru dibuang — untuk tombol "Urungkan". */
    public array $undo = [];

    /** Sertakan isi artikel di unduhan (bukan hanya metadata). */
    public bool $ikutIsi = false;

    /** Berkas tulisan yang diimpor jadi draf baru. */
    public $berkasImpor;

    public const TAB = ['all', 'published', 'terjadwal', 'draft', 'sampah'];

    public const URUT = ['baru', 'lama', 'populer', 'populer30', 'judul', 'diperbarui'];

    public const TAMPILAN = ['kartu', 'tabel'];

    /** Batas aman "pilih semua hasil" — sekali jalan tidak menyentuh ribuan baris. */
    public const BATAS_PILIH = 500;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updated($nama): void
    {
        if (in_array($nama, ['category', 'tag', 'urut', 'perPage', 'tampilan', 'fUnggulan', 'fMandek'], true)) {
            $this->resetPage();
        }

        if ($nama === 'urut' && ! in_array($this->urut, self::URUT, true)) {
            $this->urut = 'baru';
        }

        if ($nama === 'tampilan' && ! in_array($this->tampilan, self::TAMPILAN, true)) {
            $this->tampilan = 'kartu';
        }
    }

    public function clearCategory(): void
    {
        $this->category = '';
        $this->resetPage();
    }

    public function setFilter(string $f): void
    {
        $this->filter = in_array($f, self::TAB, true) ? $f : 'all';
        $this->pilih = [];
        $this->resetPage();
    }

    public function setTampilan(string $t): void
    {
        $this->tampilan = in_array($t, self::TAMPILAN, true) ? $t : 'kartu';
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'tag', 'urut', 'fUnggulan', 'fMandek']);
        $this->resetPage();
    }

    public function getAdaSaringProperty(): bool
    {
        return filled($this->search) || filled($this->category) || filled($this->tag)
            || $this->urut !== 'baru' || $this->fUnggulan || $this->fMandek;
    }

    /** Arah urut kolom tertentu di kepala tabel: 'naik' | 'turun' | null. */
    public function arahUrut(string $kolom): ?string
    {
        return match ($kolom) {
            'judul' => $this->urut === 'judul' ? 'naik' : null,
            'baca' => in_array($this->urut, ['populer', 'populer30'], true) ? 'turun' : null,
            'tanggal' => match ($this->urut) {
                'baru' => 'turun',
                'lama' => 'naik',
                default => null,
            },
            'diperbarui' => $this->urut === 'diperbarui' ? 'turun' : null,
            default => null,
        };
    }

    public function urutkanKolom(string $kolom): void
    {
        $this->urut = match ($kolom) {
            'judul' => 'judul',
            // Ditekan sekali: sepanjang masa. Ditekan lagi: 30 hari terakhir —
            // dua pertanyaan berbeda yang sering tertukar.
            'baca' => $this->urut === 'populer' ? 'populer30' : 'populer',
            'tanggal' => $this->urut === 'baru' ? 'lama' : 'baru',
            'diperbarui' => 'diperbarui',
            default => 'baru',
        };

        $this->resetPage();
    }

    // ===== Aksi satu artikel =====

    /**
     * Publikasikan / kembalikan ke draf.
     */
    public function togglePublish($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return;
        }

        if ($post->status === 'published') {
            $post->update(['status' => 'draft']);
            $this->dispatch('swal-success', message: 'Artikel dikembalikan ke draf.');
        } else {
            $post->update([
                'status' => 'published',
                'published_at' => $post->published_at ?? now(),
            ]);
            $this->dispatch('swal-success', message: 'Artikel berhasil dipublikasikan.');
        }
    }

    /** Sematkan / lepas sematan di halaman blog publik. */
    public function toggleSemat($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return;
        }

        $post->update(['is_featured' => ! $post->is_featured]);

        $this->dispatch('swal-success', message: $post->is_featured
            ? 'Artikel disematkan di atas halaman blog.'
            : 'Sematan artikel dilepas.');
    }

    /** Salin artikel jadi draf baru, lalu langsung buka penyuntingnya. */
    public function duplikat($id)
    {
        if (! auth()->user()?->hasPermission('create_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin membuat artikel.');

            return null;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return null;
        }

        $salinan = $post->duplikat();

        session()->flash('successCreated', 'Artikel disalin sebagai draf. Silakan sunting salinannya.');

        return redirect()->route('admin.blog.edit', $salinan);
    }

    /**
     * Buang ke tong sampah (bisa diurungkan), bukan hapus permanen.
     */
    public function delete($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return;
        }

        $post->delete();

        $this->undo = [(string) $post->id];
        $this->dispatch('swal-success', message: 'Artikel dipindahkan ke tong sampah.');
    }

    public function pulihkan($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        $post = BlogPost::onlyTrashed()->find($id);
        if (! $post) {
            return;
        }

        $post->restore();
        $this->undo = [];
        $this->dispatch('swal-success', message: 'Artikel dikembalikan dari tong sampah.');
    }

    public function batalkanHapus(): void
    {
        if (! $this->undo) {
            return;
        }

        BlogPost::onlyTrashed()->whereIn('id', $this->undo)->restore();
        $jumlah = count($this->undo);
        $this->undo = [];

        $this->dispatch('swal-success', message: $jumlah.' artikel dikembalikan.');
    }

    /**
     * Hapus permanen — di sinilah berkas sampulnya ikut dibuang.
     */
    public function hapusPermanen($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        $post = BlogPost::onlyTrashed()->find($id);
        if (! $post) {
            return;
        }

        $this->buangSampul($post);
        $post->forceDelete();

        $this->dispatch('swal-success', message: 'Artikel dihapus permanen.');
    }

    public function kosongkanSampah(): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        $daftar = BlogPost::onlyTrashed()->get();
        foreach ($daftar as $post) {
            $this->buangSampul($post);
            $post->forceDelete();
        }

        $this->dispatch('swal-success', message: $daftar->count().' artikel dihapus permanen.');
    }

    // ===== Aksi massal =====

    public function pilihHalaman(array $id): void
    {
        $baru = array_map('strval', $id);
        $this->pilih = array_diff($baru, $this->pilih) === []
            ? array_values(array_diff($this->pilih, $baru))
            : array_values(array_unique(array_merge($this->pilih, $baru)));
    }

    public function pilihSemuaHasil(): void
    {
        $this->pilih = $this->kueri()
            ->limit(self::BATAS_PILIH)
            ->pluck('id')
            ->map(fn ($i) => (string) $i)
            ->all();
    }

    public function lepasPilih(): void
    {
        $this->pilih = [];
    }

    public function massalStatus(string $status): void
    {
        if (! $this->bolehUbah() || ! $this->pilih) {
            return;
        }

        $jumlah = 0;
        foreach (BlogPost::whereIn('id', $this->pilih)->get() as $post) {
            $post->update($status === 'published'
                ? ['status' => 'published', 'published_at' => $post->published_at ?? now()]
                : ['status' => 'draft']);
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('swal-success', message: $jumlah.' artikel '.($status === 'published' ? 'dipublikasikan.' : 'dikembalikan ke draf.'));
    }

    public function massalKategori(string $nama): void
    {
        if (! $this->bolehUbah() || ! $this->pilih) {
            return;
        }

        $nama = trim($nama);
        $jumlah = BlogPost::whereIn('id', $this->pilih)->update(['category' => $nama ?: null]);

        $this->pilih = [];
        $this->dispatch('swal-success', message: $jumlah.' artikel dipindah ke kategori '.($nama ?: 'kosong').'.');
    }

    public function massalHapus(): void
    {
        if (! $this->bolehHapus() || ! $this->pilih) {
            return;
        }

        $id = $this->pilih;
        BlogPost::whereIn('id', $id)->delete();

        $this->undo = $id;
        $this->pilih = [];
        $this->dispatch('swal-success', message: count($id).' artikel dipindahkan ke tong sampah.');
    }

    public function massalPulihkan(): void
    {
        if (! $this->bolehHapus() || ! $this->pilih) {
            return;
        }

        $jumlah = BlogPost::onlyTrashed()->whereIn('id', $this->pilih)->restore();

        $this->pilih = [];
        $this->dispatch('swal-success', message: $jumlah.' artikel dikembalikan.');
    }

    // ===== Impor =====

    /**
     * Impor berkas tulisan (.md / .html / .txt) menjadi draf baru.
     *
     * Selalu DRAF: berkas dari luar belum tentu rapi, dan menerbitkannya
     * langsung berarti pengunjung melihat hasil konversi yang belum diperiksa.
     */
    public function updatedBerkasImpor(): void
    {
        if (! auth()->user()?->hasPermission('create_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin membuat artikel.');
            $this->reset('berkasImpor');

            return;
        }

        $this->validate([
            'berkasImpor' => 'required|file|max:2048|mimes:md,markdown,html,htm,txt',
        ], [], ['berkasImpor' => 'berkas artikel']);

        $isi = (string) file_get_contents($this->berkasImpor->getRealPath());
        $nama = pathinfo($this->berkasImpor->getClientOriginalName(), PATHINFO_FILENAME);
        $jenis = strtolower($this->berkasImpor->getClientOriginalExtension());

        [$judul, $tubuh] = ImporArtikel::urai($isi, $nama, $jenis);

        $artikel = BlogPost::create([
            'title' => $judul,
            'slug' => BlogPost::makeSlug($judul),
            'body' => $tubuh,
            'status' => 'draft',
            'author' => 'admin',
        ]);

        $this->reset('berkasImpor');
        session()->flash('successCreated', 'Berkas diimpor sebagai draf. Periksa dan rapikan sebelum diterbitkan.');

        $this->redirectRoute('admin.blog.edit', $artikel, navigate: true);
    }

    // ===== Unduhan =====

    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('view_blog'), 403);

        return Excel::download(
            new ArtikelExport($this->kueriUnduhan(), $this->ikutIsi),
            'artikel-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_blog'), 403);

        $pdf = Pdf::loadView('exports.artikel-pdf', [
            'artikel' => $this->kueriUnduhan(),
            'saringan' => $this->chipSaring(),
            'ikutIsi' => $this->ikutIsi,
        ])->setPaper('a4', $this->ikutIsi ? 'portrait' : 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), 'artikel-'.now()->format('Ymd-His').'.pdf');
    }

    /** Baris yang ikut diunduh: yang dicentang bila ada, kalau tidak seluruh hasil saringan. */
    protected function kueriUnduhan()
    {
        return $this->pilih
            ? BlogPost::withTrashed()->whereIn('id', $this->pilih)->get()
            : $this->kueri()->get();
    }

    /** Saringan aktif sebagai kalimat pendek — dipakai chip layar & kepala PDF. */
    public function chipSaring(): array
    {
        $chip = [];

        if ($this->search !== '') {
            $chip[] = ['nama' => 'search', 'label' => 'Cari: "'.$this->search.'"'];
        }

        if ($this->category !== '') {
            $chip[] = ['nama' => 'category', 'label' => 'Kategori: '.$this->category];
        }

        if ($this->tag !== '') {
            $chip[] = ['nama' => 'tag', 'label' => 'Tag: '.$this->tag];
        }

        if ($this->fUnggulan) {
            $chip[] = ['nama' => 'fUnggulan', 'label' => 'Hanya yang disematkan'];
        }

        if ($this->fMandek) {
            $chip[] = ['nama' => 'fMandek', 'label' => 'Hanya yang mandek'];
        }

        if ($this->urut !== 'baru') {
            $chip[] = ['nama' => 'urut', 'label' => 'Urut: '.[
                'lama' => 'terlama',
                'populer' => 'paling banyak dibaca',
                'populer30' => 'paling ramai 30 hari',
                'judul' => 'judul A-Z',
                'diperbarui' => 'terakhir diubah',
            ][$this->urut]];
        }

        return $chip;
    }

    public function lepasSaring(string $nama): void
    {
        if ($nama === 'urut') {
            $this->urut = 'baru';
        } elseif (in_array($nama, ['fUnggulan', 'fMandek'], true)) {
            $this->$nama = false;
        } elseif (in_array($nama, ['search', 'category', 'tag'], true)) {
            $this->$nama = '';
        }

        $this->resetPage();
    }

    // ===== Bantu =====

    private function bolehUbah(): bool
    {
        if (auth()->user()?->hasPermission('edit_blog')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah artikel.');

        return false;
    }

    private function bolehHapus(): bool
    {
        if (auth()->user()?->hasPermission('delete_blog')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus artikel.');

        return false;
    }

    private function buangSampul(BlogPost $post): void
    {
        if ($post->cover && Storage::disk('public')->exists('img/blog/'.$post->cover)) {
            Storage::disk('public')->delete('img/blog/'.$post->cover);
        }
    }

    /** Tanggal awal jendela "30 hari terakhir" — dipakai withSum dan saringan mandek. */
    private function awalPeriode(): string
    {
        return now()->subDays(29)->toDateString();
    }

    /** Kueri daftar — dipakai kartu, tabel, dan unduhan. */
    protected function kueri()
    {
        $awal = $this->awalPeriode();

        return BlogPost::query()
            ->when($this->filter === 'sampah', fn ($q) => $q->onlyTrashed())
            ->when($this->filter === 'published', fn ($q) => $q->where('status', 'published')
                ->where(fn ($s) => $s->whereNull('published_at')->orWhere('published_at', '<=', now())))
            // Terjadwal = sudah ditandai terbit, tapi waktunya belum tiba.
            ->when($this->filter === 'terjadwal', fn ($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')->where('published_at', '>', now()))
            ->when($this->filter === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            // whereJsonContains, bukan LIKE '%"tag"%': LIKE ikut mencocokkan
            // tag yang kebetulan potongan tag lain, dan salah pada tag yang
            // memuat tanda kutip.
            ->when($this->tag !== '', fn ($q) => $q->whereJsonContains('tags', $this->tag))
            ->when($this->fUnggulan, fn ($q) => $q->where('is_featured', true))
            // Mandek: sudah lama terbit dan tidak ada baca sama sekali 30 hari
            // terakhir. Dihitung di SQL supaya halaman tidak menembak satu
            // kueri per baris.
            ->when($this->fMandek, fn ($q) => $q->where('status', 'published')
                ->where(fn ($s) => $s->where('published_at', '<=', now()->subDays(BlogPost::HARI_MANDEK))
                    ->orWhere(fn ($t) => $t->whereNull('published_at')->where('created_at', '<=', now()->subDays(BlogPost::HARI_MANDEK))))
                ->whereDoesntHave('bacaHarian', fn ($s) => $s->where('tanggal', '>=', $awal)))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('excerpt', 'like', $term)
                        ->orWhere('tags', 'like', $term)
                        // Isi artikel ikut dicari: kalimat yang diingat orang
                        // biasanya ada di badan tulisan, bukan judulnya.
                        ->orWhere('body', 'like', $term);
                });
            })
            ->with('penyunting:id,name')
            ->withSum(['bacaHarian as baca_30' => fn ($q) => $q->where('tanggal', '>=', $awal)], 'jumlah')
            // Artikel yang disematkan selalu di atas, apa pun urutannya.
            ->orderByDesc('is_featured')
            ->when($this->urut === 'baru', fn ($q) => $q->latest())
            ->when($this->urut === 'lama', fn ($q) => $q->oldest())
            ->when($this->urut === 'populer', fn ($q) => $q->orderByDesc('views')->latest())
            ->when($this->urut === 'populer30', fn ($q) => $q->orderByDesc('baca_30')->latest())
            ->when($this->urut === 'judul', fn ($q) => $q->orderBy('title'))
            ->when($this->urut === 'diperbarui', fn ($q) => $q->orderByDesc('updated_at'));
    }

    public function render()
    {
        $posts = $this->kueri()->paginate(max(6, min(48, $this->perPage)));
        $awal = $this->awalPeriode();

        // Satu kueri untuk hitungan status; sisanya dihitung dari situ.
        $perStatus = BlogPost::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $terjadwal = BlogPost::where('status', 'published')->whereNotNull('published_at')->where('published_at', '>', now())->count();

        $tabCounts = [
            'all' => (int) $perStatus->sum(),
            'published' => (int) ($perStatus['published'] ?? 0) - $terjadwal,
            'terjadwal' => $terjadwal,
            'draft' => (int) ($perStatus['draft'] ?? 0),
            'sampah' => BlogPost::onlyTrashed()->count(),
        ];

        return view('livewire.pages.admin.blog.blog-list', [
            'posts' => $posts,
            'tabCounts' => $tabCounts,
            'kategoriDaftar' => BlogCategory::orderBy('name')->pluck('name')->all(),
            'tagDaftar' => $this->semuaTag(),
            // Angka ringkasan: yang paling menjawab "blognya hidup atau tidak".
            'totalDibaca' => (int) BlogPost::sum('views'),
            'dibaca30' => (int) \App\Models\BlogPostRead::where('tanggal', '>=', $awal)->sum('jumlah'),
            'dibaca30Sebelumnya' => (int) \App\Models\BlogPostRead::whereBetween('tanggal', [
                now()->subDays(59)->toDateString(),
                now()->subDays(30)->toDateString(),
            ])->sum('jumlah'),
            'terpopuler' => BlogPost::where('views', '>', 0)->orderByDesc('views')->first(),
            'terbaru' => BlogPost::published()->latest('published_at')->first(),
            'jumlahMandek' => $this->hitungMandek(),
        ])->layout('livewire.layout.templateindex');
    }

    /** Semua tag yang pernah dipakai, tanpa duplikat. */
    private function semuaTag(): array
    {
        $tag = BlogPost::whereNotNull('tags')->pluck('tags')->flatMap(fn ($t) => (array) $t)->all();

        $bersih = array_values(array_unique(array_filter(array_map('trim', $tag), 'strlen')));
        sort($bersih, SORT_NATURAL | SORT_FLAG_CASE);

        return $bersih;
    }

    private function hitungMandek(): int
    {
        $awal = $this->awalPeriode();
        $batas = now()->subDays(BlogPost::HARI_MANDEK);

        return BlogPost::where('status', 'published')
            ->where(fn ($s) => $s->where('published_at', '<=', $batas)
                ->orWhere(fn ($t) => $t->whereNull('published_at')->where('created_at', '<=', $batas)))
            ->whereDoesntHave('bacaHarian', fn ($s) => $s->where('tanggal', '>=', $awal))
            ->count();
    }
}
