<?php

namespace App\Livewire\Pages\Admin\Ebook;

use App\Models\Ebook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EbookList extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    /** '' | active | non-active */
    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    /** Ebook yang sedang dibuka di jendela detail. */
    public ?string $lihatId = null;

    /** Jendela "terapkan saran ebook bawaan" terbuka? */
    public bool $saranBuka = false;

    /** Produk yang dicentang di jendela saran (id produk => id ebook). */
    public array $saranPilih = [];

    /** Minimal berapa kali dikirim bersama sebelum disarankan. */
    public const SARAN_MIN = 3;

    /**
     * Untuk tiap produk akun: ebook AKTIF yang paling sering dikirim bersamanya
     * (riwayat order_item_ebook), minimal SARAN_MIN kali.
     *
     * @return \Illuminate\Support\Collection<int, array{produk: \App\Models\Product, ebook: Ebook, n: int, sekarang: ?Ebook}>
     */
    protected function saranBawaan()
    {
        $baris = DB::table('order_item_ebook')
            ->join('order_items', 'order_items.id', '=', 'order_item_ebook.order_item_id')
            ->join('ebooks', 'ebooks.id', '=', 'order_item_ebook.ebook_id')
            ->where('ebooks.status', 'active')
            ->whereNull('ebooks.deleted_at')
            ->whereNotNull('order_items.product_id')
            ->selectRaw('order_items.product_id, order_item_ebook.ebook_id, count(*) as n')
            ->groupBy('order_items.product_id', 'order_item_ebook.ebook_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($g) => $g->sortByDesc('n')->first())
            ->filter(fn ($r) => self::SARAN_MIN <= $r->n);

        $produk = \App\Models\Product::with('ebookBawaan:id,judul')->whereIn('id', $baris->keys())->get()->keyBy('id');
        $ebook = Ebook::whereIn('id', $baris->pluck('ebook_id'))->get()->keyBy('id');

        return $baris->map(fn ($r) => [
            'produk' => $produk->get($r->product_id),
            'ebook' => $ebook->get($r->ebook_id),
            'n' => (int) $r->n,
            'sekarang' => $produk->get($r->product_id)?->ebookBawaan,
        ])
            ->filter(fn ($x) => $x['produk'] && $x['ebook'] && ! $x['produk']->butuh_file)
            // Yang sudah sesuai tidak perlu ditampilkan.
            ->reject(fn ($x) => $x['produk']->ebook_bawaan_id === $x['ebook']->id)
            ->sortByDesc('n')
            ->values();
    }

    public function bukaSaran(): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_ebook'), 403);

        // Tercentang bawaan: produk yang BELUM punya ebook bawaan. Yang sudah
        // punya (berbeda) dibiarkan tidak tercentang — admin memutuskan sendiri.
        $this->saranPilih = $this->saranBawaan()
            ->filter(fn ($x) => ! $x['sekarang'])
            ->mapWithKeys(fn ($x) => [(string) $x['produk']->id => (string) $x['ebook']->id])
            ->all();
        $this->saranBuka = true;
    }

    public function tutupSaran(): void
    {
        $this->saranBuka = false;
    }

    public function alihSaran(string $produkId, string $ebookId): void
    {
        if (isset($this->saranPilih[$produkId])) {
            unset($this->saranPilih[$produkId]);
        } else {
            $this->saranPilih[$produkId] = $ebookId;
        }
    }

    public function terapkanSaran(): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_ebook'), 403);

        // Hanya pasangan yang memang ada di saran (nilai dari peramban tidak dipercaya).
        $sah = $this->saranBawaan()->mapWithKeys(fn ($x) => [(string) $x['produk']->id => (string) $x['ebook']->id]);
        $n = 0;
        foreach ($this->saranPilih as $produkId => $ebookId) {
            if (($sah[$produkId] ?? null) === $ebookId) {
                \App\Models\Product::whereKey($produkId)->update(['ebook_bawaan_id' => $ebookId]);
                $n++;
            }
        }

        $this->saranBuka = false;
        $this->saranPilih = [];
        $this->dispatch('swal-success', message: $n.' produk kini punya ebook bawaan.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function lihat(string $id): void
    {
        $this->lihatId = Ebook::whereKey($id)->value('id');
    }

    public function tutupLihat(): void
    {
        $this->lihatId = null;
    }

    /**
     * Ganti tautan pelanggan (share_token). Tautan lama langsung mati untuk
     * SEMUA penerima — dipakai bila tautannya tersebar ke luar.
     */
    public function buatTautanBaru(string $id): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_ebook'), 403);

        $ebook = Ebook::findOrFail($id);
        $ebook->update(['share_token' => Ebook::generateToken()]);

        $this->dispatch('swal-success', message: 'Tautan baru dibuat. Tautan lama sudah tidak bisa dibuka.');
    }

    public function deleteEbook($id)
    {
        if (! auth()->user()->hasPermission('delete_ebook')) {
            $this->dispatch('Ebook-deleteError', message: 'Anda tidak memiliki izin menghapus ebook.');

            return;
        }

        $ebook = Ebook::find($id);

        if (! $ebook) {
            $this->dispatch('Ebook-deleteError', message: 'Data ebook tidak ditemukan!');

            return;
        }

        if ($ebook->file && Storage::disk('local')->exists('ebooks/'.$ebook->file)) {
            Storage::disk('local')->delete('ebooks/'.$ebook->file);
        }

        $ebook->delete();
        $this->lihatId = null;

        $this->dispatch('Ebook-deleted');
    }

    public function render()
    {
        $ebooks = Ebook::query()
            ->withCount(['orderItems', 'produkBawaan'])
            ->when($this->search, function ($q) {
                $q->where(fn ($s) => $s->where('judul', 'like', "%{$this->search}%")
                    ->orWhere('deskripsi', 'like', "%{$this->search}%"));
            })
            ->when(in_array($this->statusFilter, ['active', 'non-active'], true), fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(12);

        $detail = $this->lihatId ? Ebook::withCount('orderItems')->with('produkBawaan:id,nama_akun,ebook_bawaan_id')->find($this->lihatId) : null;

        return view('livewire.pages.admin.ebook.ebook-list', [
            'ebooks' => $ebooks,
            'ringkas' => [
                'total' => Ebook::count(),
                'aktif' => Ebook::where('status', 'active')->count(),
                'nonaktif' => Ebook::where('status', '!=', 'active')->count(),
                // Berapa kali ebook dikirim sebagai bonus (baris pivot item pesanan).
                'dikirim' => DB::table('order_item_ebook')->count(),
            ],
            'detail' => $detail,
            'saran' => $this->saranBuka ? $this->saranBawaan() : collect(),
            'jumlahSaran' => $this->saranBawaan()->count(),
            // Pesanan terbaru yang menerima ebook ini (untuk jendela detail).
            'detailPesanan' => $detail
                ? $detail->orderItems()->with('order:id,order_number,customer_id', 'order.customer:id,nama')
                    ->latest('order_item_ebook.created_at')->limit(5)->get()
                : collect(),
        ])->layout('livewire.layout.templateindex');
    }
}
