<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Exports\ArtikelExport;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class BlogList extends Component
{
    use WithPagination;

    /** all | published | terjadwal | draft — tab yang sekaligus kartu hitungan. */
    #[Url(as: 'tab', except: 'all')]
    public string $filter = 'all';

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    #[Url(as: 'category', keep: false)]
    public string $category = '';

    /** baru | lama | populer | judul */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    /** kartu | tabel */
    #[Url(as: 'tampilan', except: 'kartu')]
    public string $tampilan = 'kartu';

    #[Url(as: 'per', except: 12)]
    public int $perPage = 12;

    public const TAB = ['all', 'published', 'terjadwal', 'draft'];

    public const URUT = ['baru', 'lama', 'populer', 'judul'];

    public const TAMPILAN = ['kartu', 'tabel'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updated($nama): void
    {
        if (in_array($nama, ['category', 'urut', 'perPage', 'tampilan'], true)) {
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
        $this->resetPage();
    }

    public function setTampilan(string $t): void
    {
        $this->tampilan = in_array($t, self::TAMPILAN, true) ? $t : 'kartu';
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'urut']);
        $this->resetPage();
    }

    public function getAdaSaringProperty(): bool
    {
        return filled($this->search) || filled($this->category) || $this->urut !== 'baru';
    }

    /**
     * Publikasikan / kembalikan ke draf.
     */
    public function togglePublish($id): void
    {
        if (! auth()->user()->hasPermission('edit_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah artikel.');

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

    public function delete($id): void
    {
        if (! auth()->user()->hasPermission('delete_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus artikel.');

            return;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return;
        }

        if ($post->cover && Storage::disk('public')->exists('img/blog/'.$post->cover)) {
            Storage::disk('public')->delete('img/blog/'.$post->cover);
        }

        $post->delete();
        $this->dispatch('swal-success', message: 'Artikel berhasil dihapus.');
    }

    // ===== Unduhan =====

    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('view_blog'), 403);

        return Excel::download(new ArtikelExport($this->kueri()->get()), 'artikel-'.now()->format('Ymd-His').'.xlsx');
    }

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_blog'), 403);

        $pdf = Pdf::loadView('exports.artikel-pdf', [
            'artikel' => $this->kueri()->get(),
            'saringan' => $this->chipSaring(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), 'artikel-'.now()->format('Ymd-His').'.pdf');
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

        if ($this->urut !== 'baru') {
            $chip[] = ['nama' => 'urut', 'label' => 'Urut: '.[
                'lama' => 'terlama',
                'populer' => 'paling banyak dibaca',
                'judul' => 'judul A-Z',
            ][$this->urut]];
        }

        return $chip;
    }

    public function lepasSaring(string $nama): void
    {
        if ($nama === 'urut') {
            $this->urut = 'baru';
        } elseif (in_array($nama, ['search', 'category'], true)) {
            $this->$nama = '';
        }

        $this->resetPage();
    }

    /** Kueri daftar — dipakai kartu, tabel, dan unduhan. */
    protected function kueri()
    {
        return BlogPost::query()
            ->when($this->filter === 'published', fn ($q) => $q->where('status', 'published')
                ->where(fn ($s) => $s->whereNull('published_at')->orWhere('published_at', '<=', now())))
            // Terjadwal = sudah ditandai terbit, tapi waktunya belum tiba.
            ->when($this->filter === 'terjadwal', fn ($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')->where('published_at', '>', now()))
            ->when($this->filter === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('excerpt', 'like', $term);
                });
            })
            ->when($this->urut === 'baru', fn ($q) => $q->latest())
            ->when($this->urut === 'lama', fn ($q) => $q->oldest())
            ->when($this->urut === 'populer', fn ($q) => $q->orderByDesc('views')->latest())
            ->when($this->urut === 'judul', fn ($q) => $q->orderBy('title'));
    }

    public function render()
    {
        $posts = $this->kueri()->paginate(max(6, min(48, $this->perPage)));

        // Satu kueri untuk hitungan status; sisanya dihitung dari situ.
        $perStatus = BlogPost::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $terjadwal = BlogPost::where('status', 'published')->whereNotNull('published_at')->where('published_at', '>', now())->count();

        $tabCounts = [
            'all' => (int) $perStatus->sum(),
            'published' => (int) ($perStatus['published'] ?? 0) - $terjadwal,
            'terjadwal' => $terjadwal,
            'draft' => (int) ($perStatus['draft'] ?? 0),
        ];

        return view('livewire.pages.admin.blog.blog-list', [
            'posts' => $posts,
            'tabCounts' => $tabCounts,
            'kategoriDaftar' => BlogCategory::orderBy('name')->pluck('name')->all(),
            // Angka ringkasan: yang paling menjawab "blognya hidup atau tidak".
            'totalDibaca' => (int) BlogPost::sum('views'),
            'terpopuler' => BlogPost::where('views', '>', 0)->orderByDesc('views')->first(),
            'terbaru' => BlogPost::published()->latest('published_at')->first(),
        ])->layout('livewire.layout.templateindex');
    }
}
