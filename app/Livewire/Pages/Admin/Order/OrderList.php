<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CariNoHp;
use App\Support\PengingatPerpanjangan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    // Penanda waktu pembayaran terakhir yang sudah "dilihat" (untuk notifikasi polling)
    public string $lastPaidMarker = '';

    public string $activeTab = 'all';

    /*
     * Saringan disimpan di alamat halaman (#[Url]) supaya tetap ada saat admin
     * membuka detail pesanan lalu menekan Kembali. Sebelumnya hanya tab yang
     * diingat — pencarian harus diketik ulang setiap kali.
     */
    #[Url(as: 'cari', except: '')]
    public string $search = '';

    #[Url(as: 'bulan', except: '')]
    public string $filterMonth = '';

    #[Url(as: 'tahun', except: '')]
    public string $filterYear = '';

    #[Url(as: 'dari', except: '')]
    public string $tglDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $tglSampai = '';

    #[Url(as: 'metode', except: '')]
    public string $metode = '';

    #[Url(as: 'produk', except: '')]
    public string $produk = '';

    /** '' | jasa | akun */
    #[Url(as: 'jenis', except: '')]
    public string $jenis = '';

    #[Url(as: 'urut', except: 'terbaru')]
    public string $urut = 'terbaru';

    #[Url(as: 'baris', except: 10)]
    public int $perHalaman = 10;

    /** Item terpilih untuk aksi massal (tab Segera Habis / Akun Habis). */
    public array $terpilih = [];

    public const URUTAN = [
        'terbaru' => 'Terbaru dibuat',
        'terlama' => 'Terlama dibuat',
        'dibayar' => 'Terakhir dibayar',
        'total_tinggi' => 'Total terbesar',
        'total_rendah' => 'Total terkecil',
    ];

    public const PILIHAN_BARIS = [10, 25, 50];

    public const METODE = [
        'qris_dinamis' => 'QRIS Dinamis',
        'qris_statis' => 'QRIS Statis',
        'transfer' => 'Transfer Bank',
    ];

    protected $queryString = ['activeTab'];

    public function mount(): void
    {
        // Hanya pembayaran SETELAH halaman dibuka yang akan memunculkan notifikasi
        $this->lastPaidMarker = now()->toDateTimeString();
    }

    /**
     * Polling ringan: deteksi pembayaran baru → tampilkan notifikasi glossy.
     * Daftar & counter ikut ter-refresh karena method ini memicu re-render.
     */
    public function watchNewPayments(): void
    {
        $new = Order::whereNotNull('paid_at')
            ->where('paid_at', '>', $this->lastPaidMarker)
            ->orderBy('paid_at')
            ->with('customer')
            ->get(['id', 'order_number', 'total', 'paid_at', 'customer_id']);

        if ($new->isEmpty()) {
            return;
        }

        // Majukan penanda ke pembayaran terbaru
        $this->lastPaidMarker = (string) $new->last()->paid_at;

        $latest = $new->last();
        $this->dispatch(
            'order-paid-toast',
            orderNumber: $latest->order_number,
            customerName: $latest->customer->nama ?? 'Pelanggan',
            total: (int) round((float) $latest->total),
        );
    }

    /** Saringan apa pun berubah → kembali ke halaman 1 & kosongkan pilihan. */
    public function updated($nama): void
    {
        if (in_array($nama, ['search', 'filterMonth', 'filterYear', 'tglDari', 'tglSampai', 'metode', 'produk', 'jenis', 'urut', 'perHalaman'], true)) {
            if ($nama === 'urut' && ! array_key_exists($this->urut, self::URUTAN)) {
                $this->urut = 'terbaru';
            }
            if ($nama === 'perHalaman' && ! in_array($this->perHalaman, self::PILIHAN_BARIS, true)) {
                $this->perHalaman = 10;
            }
            $this->terpilih = [];
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterMonth', 'filterYear', 'tglDari', 'tglSampai', 'metode', 'produk', 'jenis', 'terpilih']);
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->terpilih = [];
        $this->resetPage();
    }

    /** Jumlah saringan lanjutan yang aktif (untuk lencana tombol "Saringan lanjutan"). */
    public function jumlahSaringanLanjut(): int
    {
        return collect([$this->tglDari, $this->tglSampai, $this->metode, $this->produk, $this->jenis])->filter()->count();
    }

    /** Tanggal Y-m-d yang sah, atau null (nilai dari URL bisa dikarang). */
    private function tanggal(string $nilai): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai) ? $nilai : null;
    }

    /** Saringan produk & jenis pada kueri OrderItem. */
    private function saringItem($q)
    {
        return $q->when($this->produk, fn ($x) => $x->where('product_id', $this->produk))
            ->when($this->jenis === 'jasa', fn ($x) => $x->whereHas('product', fn ($p) => $p->where('butuh_file', true)))
            ->when($this->jenis === 'akun', fn ($x) => $x->whereDoesntHave('product', fn ($p) => $p->where('butuh_file', true)));
    }

    /**
     * Query dasar Order dengan filter search + periode (bulan/tahun) diterapkan,
     * TANPA filter tab. Dipakai bersama oleh daftar & penghitung tab agar angka
     * di tab ikut berubah saat difilter/dicari.
     */
    protected function baseOrderQuery()
    {
        return Order::query()
            ->when($this->search, function ($q) {
                $term = $this->search;
                // Nomor HP dicocokkan lewat bentuk INTI supaya "+62 813-3913-9595"
                // (disalin dari WhatsApp) tetap menemukan "081339139595" di DB.
                $intiHp = CariNoHp::inti($term);
                $q->where(function ($sub) use ($term, $intiHp) {
                    $sub->where('order_number', 'like', "%{$term}%")
                        ->orWhere('status', 'like', "%{$term}%")
                        ->orWhere('payment_method', 'like', "%{$term}%")
                        ->orWhere('customer_notes', 'like', "%{$term}%")
                        ->orWhereHas('customer', function ($c) use ($term, $intiHp) {
                            $c->where('nama', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('no_hp', 'like', "%{$term}%")
                                ->when($intiHp, fn ($x) => $x->orWhereRaw(
                                    CariNoHp::kolomBersih('no_hp').' LIKE ?', ["%{$intiHp}%"]
                                ));
                        })
                        ->orWhereHas('items', function ($it) use ($term) {
                            $it->where('product_name', 'like', "%{$term}%")
                                ->orWhere('account_username', 'like', "%{$term}%")
                                ->orWhere('account_link', 'like', "%{$term}%")
                                ->orWhere('subscription_status', 'like', "%{$term}%")
                                ->orWhere('delivery_status', 'like', "%{$term}%");
                        });
                });
            })
            ->when($this->filterMonth, function ($q) {
                $q->whereMonth('created_at', $this->filterMonth);
            })
            ->when($this->filterYear, function ($q) {
                $q->whereYear('created_at', $this->filterYear);
            })
            ->when($this->tanggal($this->tglDari), fn ($q, $t) => $q->whereDate('created_at', '>=', $t))
            ->when($this->tanggal($this->tglSampai), fn ($q, $t) => $q->whereDate('created_at', '<=', $t))
            ->when(array_key_exists($this->metode, self::METODE), fn ($q) => $q->where('payment_method', $this->metode))
            ->when($this->produk || $this->jenis, function ($q) {
                // "akun" = pesanan tanpa satu pun item jasa.
                if ($this->jenis === 'akun') {
                    $q->whereDoesntHave('items.product', fn ($p) => $p->where('butuh_file', true));
                    if ($this->produk) {
                        $q->whereHas('items', fn ($i) => $i->where('product_id', $this->produk));
                    }

                    return;
                }
                $q->whereHas('items', fn ($i) => $this->saringItem($i));
            });
    }

    /** Urutan daftar pesanan sesuai pilihan admin. */
    private function urutkan($q)
    {
        return match ($this->urut) {
            'terlama' => $q->oldest(),
            'dibayar' => $q->orderByRaw('paid_at IS NULL')->orderByDesc('paid_at')->latest(),
            'total_tinggi' => $q->orderByDesc('total')->latest(),
            'total_rendah' => $q->orderBy('total')->latest(),
            default => $q->latest(),
        };
    }

    /** Kueri pesanan untuk tab aktif (tanpa urutan & halaman) — dipakai daftar dan ekspor. */
    protected function kueriTab()
    {
        return $this->baseOrderQuery()
            ->when($this->activeTab === 'processing', fn ($q) => $q->where('status', 'processing'))
            ->when($this->activeTab === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->when($this->activeTab === 'neworder', fn ($q) => $q->whereIn('status', ['pending', 'paid'])
                ->whereDoesntHave('uploads', fn ($u) => $u->where('status', 'selesai')))
            ->when($this->activeTab === 'berjalan', fn ($q) => $q->pengecekanBerjalan())
            ->when($this->activeTab === 'cancelled', fn ($q) => $q->where('status', 'cancelled'))
            ->when($this->activeTab === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($this->activeTab === 'catatan', fn ($q) => $this->punyaCatatan($q))
            ->when($this->activeTab !== 'draft', fn ($q) => $q->where('status', '!=', 'draft'));
    }

    public function getOrdersProperty()
    {
        return $this->urutkan($this->kueriTab()
            // items.product & uploads dimuat utk Order::labelStatus() (label kerja
            // jasa) tanpa N+1 per baris.
            ->with('customer', 'items.product', 'uploads')
            // Penanda pengecekan di daftar agar admin tak perlu membuka tiap
            // pesanan: yang MENUNGGU (belum disentuh) & yang SEDANG DIPROSES.
            ->withCount([
                'uploads as pengecekan_menunggu_count' => fn ($q) => $q->where('status', 'menunggu'),
                'uploads as pengecekan_diproses_count' => fn ($q) => $q->where('status', 'diproses'),
            ]))
            ->paginate($this->perHalaman);
    }

    /** Pesanan dengan catatan yang masih perlu ditindaklanjuti (lihat Order::scopeCatatanTerbuka). */
    protected function punyaCatatan($q)
    {
        return $q->catatanTerbuka();
    }

    /**
     * Tandai semua catatan pesanan ini selesai: catatan internal di item
     * dihapus, catatan pelanggan ditandai ditangani (tidak dihapus).
     */
    public function selesaikanCatatan(string $orderId): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_pemesanantoko'), 403);

        $order = Order::with('items')->findOrFail($orderId);
        \App\Support\CatatanPesanan::selesaikanSemua($order);

        $this->dispatch('catatan-diselesaikan', pesan: 'Catatan pesanan '.$order->order_number.' ditandai selesai.');
    }

    // Tab "Akun Habis" menampilkan ITEM yang habis (bukan order),
    // karena satu order bisa terdiri dari beberapa item dengan masa aktif berbeda.
    /**
     * Query dasar item "Akun Habis" dengan filter search + periode diterapkan,
     * dipakai bersama oleh daftar & penghitung tab habis.
     */
    protected function baseHabisQuery()
    {
        return $this->saringKueriItem(OrderItem::query()
            ->where(function ($q) {
                $q->where('subscription_status', 'habis')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('end_date')
                            ->where('end_date', '<', now());
                    });
            }));
    }

    /** Item yang masa aktifnya berakhir dalam 7 hari (lihat PengingatPerpanjangan). */
    protected function baseSegeraQuery()
    {
        return $this->saringKueriItem(PengingatPerpanjangan::scopeSegeraHabis(OrderItem::query()));
    }

    /** Cari + periode + produk/jenis untuk tab berbasis item (Segera Habis, Akun Habis). */
    protected function saringKueriItem($kueri)
    {
        return $this->saringItem($kueri)
            ->when($this->search, function ($q) {
                $term = $this->search;
                $intiHp = CariNoHp::inti($term);
                $q->where(function ($sub) use ($term, $intiHp) {
                    $sub->where('product_name', 'like', "%{$term}%")
                        ->orWhere('account_username', 'like', "%{$term}%")
                        ->orWhere('account_link', 'like', "%{$term}%")
                        ->orWhere('subscription_status', 'like', "%{$term}%")
                        ->orWhereHas('order', function ($o) use ($term, $intiHp) {
                            $o->where('order_number', 'like', "%{$term}%")
                                ->orWhereHas('customer', function ($c) use ($term, $intiHp) {
                                    $c->where('nama', 'like', "%{$term}%")
                                        ->orWhere('email', 'like', "%{$term}%")
                                        ->orWhere('no_hp', 'like', "%{$term}%")
                                        ->when($intiHp, fn ($x) => $x->orWhereRaw(
                                            CariNoHp::kolomBersih('no_hp').' LIKE ?', ["%{$intiHp}%"]
                                        ));
                                });
                        });
                });
            })
            ->when($this->filterMonth || $this->filterYear, function ($q) {
                $q->whereHas('order', function ($q2) {
                    $q2->when($this->filterMonth, fn ($x) => $x->whereMonth('created_at', $this->filterMonth))
                        ->when($this->filterYear, fn ($x) => $x->whereYear('created_at', $this->filterYear));
                });
            })
            ->when($this->tanggal($this->tglDari) || $this->tanggal($this->tglSampai) || array_key_exists($this->metode, self::METODE), function ($q) {
                $q->whereHas('order', fn ($o) => $o
                    ->when($this->tanggal($this->tglDari), fn ($x, $t) => $x->whereDate('created_at', '>=', $t))
                    ->when($this->tanggal($this->tglSampai), fn ($x, $t) => $x->whereDate('created_at', '<=', $t))
                    ->when(array_key_exists($this->metode, self::METODE), fn ($x) => $x->where('payment_method', $this->metode)));
            });
    }

    public function getHabisItemsProperty()
    {
        return $this->baseHabisQuery()
            ->with('order.customer', 'product')
            ->orderBy('end_date', 'desc')
            ->paginate($this->perHalaman);
    }

    public function getSegeraItemsProperty()
    {
        return $this->baseSegeraQuery()
            ->with('order.customer', 'product')
            ->orderBy('end_date')
            ->paginate($this->perHalaman);
    }

    /** Tab berbasis item (satu baris = satu akun), bukan pesanan. */
    public function tabItem(): bool
    {
        return in_array($this->activeTab, ['segera', 'habis'], true);
    }

    /**
     * Tandai pelanggan sudah dihubungi — satu item (dari tombol WA) atau
     * semua yang dicentang. Segera Habis → ingat_perpanjang_at, Akun Habis →
     * habis_notified_at (kolom lama yang juga dipakai detail pesanan).
     */
    public function tandaiDihubungi(?string $itemId = null): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_pemesanantoko'), 403);

        $kolom = $this->activeTab === 'segera' ? 'ingat_perpanjang_at' : 'habis_notified_at';
        $kueri = $this->activeTab === 'segera' ? $this->baseSegeraQuery() : $this->baseHabisQuery();
        $id = $itemId ? [$itemId] : array_values(array_filter($this->terpilih, 'is_string'));
        if (! $this->tabItem() || ! $id) {
            return;
        }

        // Satu per satu lewat Eloquent supaya riwayat pesanan ikut tercatat.
        $n = 0;
        foreach ($kueri->whereIn('order_items.id', $id)->get() as $item) {
            $item->update([$kolom => now()]);
            $n++;
        }
        $this->terpilih = [];

        if (! $itemId) {
            $this->dispatch('swal-success', message: $n.' akun ditandai sudah dihubungi.');
        }
    }

    /**
     * Unduh Excel sesuai tab & saringan yang sedang tampil.
     * Nomor HP pelanggan ikut terbawa, jadi hanya untuk pengelola pesanan.
     */
    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('edit_pemesanantoko'), 403);

        $nama = 'Pesanan-Toko_'.$this->activeTab.'_'.now()->format('Ymd-His').'.xlsx';

        if ($this->tabItem()) {
            $items = ($this->activeTab === 'segera' ? $this->baseSegeraQuery()->orderBy('end_date') : $this->baseHabisQuery()->orderByDesc('end_date'))
                ->with('order.customer')->get();

            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PesananTokoExport(null, $items), $nama);
        }

        $orders = $this->urutkan($this->kueriTab()->with('customer', 'items'))->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PesananTokoExport($orders, null), $nama);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $habisItemsCount = $this->baseHabisQuery()->count();

        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM'),
        ]);

        // Rentang tahun dari pesanan tertua s/d terbaru. Bukan YEAR() milik
        // MySQL, supaya daftar ini juga bisa dirender di basis data uji.
        $tertua = Order::min('created_at');
        $terbaru = Order::max('created_at');
        $years = $tertua
            ? collect(range((int) substr((string) $terbaru, 0, 4), (int) substr((string) $tertua, 0, 4)))
            : collect();

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.pages.admin.order.order-list', [
            'orders' => $this->tabItem() ? null : $this->orders,
            'habisItems' => $this->activeTab === 'habis' ? $this->habisItems : null,
            'segeraItems' => $this->activeTab === 'segera' ? $this->segeraItems : null,
            'produkPilihan' => Product::whereIn('id', OrderItem::query()->select('product_id')->distinct())
                ->orderBy('nama_akun')->get(['id', 'nama_akun']),
            'months' => $months,
            'years' => $years,
            // Penghitung tab ikut menerapkan filter search + periode agar angka
            // berubah sesuai data yang ditampilkan saat difilter/dicari.
            'tabCounts' => [
                'all' => $this->baseOrderQuery()->where('status', '!=', 'draft')->count(),
                'neworder' => $this->baseOrderQuery()->whereIn('status', ['pending', 'paid'])
                    ->whereDoesntHave('uploads', fn ($u) => $u->where('status', 'selesai'))->count(),
                'berjalan' => $this->baseOrderQuery()->pengecekanBerjalan()->count(),
                'processing' => $this->baseOrderQuery()->where('status', 'processing')->count(),
                'completed' => $this->baseOrderQuery()->where('status', 'completed')->count(),
                'cancelled' => $this->baseOrderQuery()->where('status', 'cancelled')->count(),
                'draft' => $this->baseOrderQuery()->where('status', 'draft')->count(),
                'catatan' => $this->punyaCatatan($this->baseOrderQuery()->where('status', '!=', 'draft'))->count(),
                'segera' => $this->baseSegeraQuery()->count(),
                'habis' => $habisItemsCount,
            ],
        ]);
    }
}
