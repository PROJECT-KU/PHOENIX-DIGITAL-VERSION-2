<?php

namespace App\Livewire\Pages\Admin\Modal;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\Modal;
use App\Models\Setting;
use App\Models\Spending;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ModalList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $bulan = '';

    public $tahun = '';

    public $targetInput = '';

    /* ===== Form top-up modal operasional (CRUD via modal) ===== */
    public bool $showForm = false;

    public ?string $editingId = null;

    public $formTanggal = '';

    public $formNominal = '';

    public $formDeskripsi = '';

    public bool $showTarget = false;

    /* ===== Paginasi rincian modal per produk (in-memory) ===== */
    public $akunPage = 1;

    public $akunPerPage = 8;

    protected $queryString = [
        'search' => ['except' => ''],
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->targetInput = (string) (int) Setting::get('modal_operasional_target', 0);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBulan(): void
    {
        $this->resetPage();
        $this->akunPage = 1;
    }

    public function updatedTahun(): void
    {
        $this->resetPage();
        $this->akunPage = 1;
    }

    public function akunNext(): void
    {
        $this->akunPage++;
    }

    public function akunPrev(): void
    {
        if ($this->akunPage > 1) {
            $this->akunPage--;
        }
    }

    public function akunGoto($page): void
    {
        $this->akunPage = max(1, (int) $page);
    }

    public function resetFilter(): void
    {
        $this->reset(['search']);
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->resetPage();
    }

    private function bolehKelola(): bool
    {
        return auth()->user()?->hasPermission('create_modal') ?? false;
    }

    private function toNumber($value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }

    /**
     * Saldo awal periode = akumulasi (setoran - terpakai operasional) sebelum bulan ini,
     * dihitung MULAI dari bulan top-up modal pertama (agar pengeluaran lama sebelum
     * modal dipakai tidak ikut mengurangi saldo).
     */
    private function hitungSaldoAwal(int $bulan, int $tahun): float
    {
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();

        $mulai = Modal::operasional()->min('tanggal');
        if (! $mulai) {
            return 0.0;
        }
        $mulaiBulan = Carbon::parse($mulai)->startOfMonth()->toDateString();

        // Jika bulan ini adalah bulan mulai (atau sebelumnya) -> belum ada saldo awal.
        if ($start <= $mulaiBulan) {
            return 0.0;
        }

        $setoranSebelum = (float) Modal::operasional()
            ->where('tanggal', '<', $start)->sum('nominal');
        $terpakaiSebelum = (float) $this->terpakaiOperasionalQuery()
            ->where('tanggal_transaksi', '>=', $mulaiBulan)
            ->where('tanggal_transaksi', '<', $start)->sum('nominal')
            + $this->terpakaiPrivateRange($mulaiBulan, $start);

        return $setoranSebelum - $terpakaiSebelum;
    }

    /**
     * Pengeluaran (Spending) yang mengurangi MODAL OPERASIONAL:
     * semua "lainnya" + "pembelian_akun" produk NON-private.
     * (Private = katalog, biayanya dihitung per-order via terpakaiPrivateRange.)
     */
    private function terpakaiOperasionalQuery()
    {
        $privateIds = \App\Models\Product::where('tipe_akun', 'private')->pluck('id')->all();

        return Spending::query()->where(function ($q) use ($privateIds) {
            $q->where('jenis_pengeluaran', 'lainnya')
                ->orWhere(function ($x) use ($privateIds) {
                    $x->where('jenis_pengeluaran', 'pembelian_akun');
                    if (! empty($privateIds)) {
                        $x->where(function ($y) use ($privateIds) {
                            $y->whereNull('product_id')->orWhereNotIn('product_id', $privateIds);
                        });
                    }
                });
        });
    }

    /**
     * Biaya modal akun PRIVATE (kas nyata) pada rentang tanggal =
     * total expense "Modal Akun Private" di cash flow (per order, satuan x qty).
     */
    private function terpakaiPrivateRange($start, $endExclusive): float
    {
        return (float) \App\Models\CashFlow::where('category', 'Modal Akun Private')
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $start)
            ->where('transaction_date', '<', $endExclusive)
            ->sum('amount');
    }

    private function syncCashFlow(Modal $m): void
    {
        app(SyncCashFlowAction::class)->execute($m, [
            'amount' => (float) $m->nominal,
            'type' => 'income',
            'date' => $m->tanggal,
            'category' => 'Modal Operasional',
            'description' => $m->deskripsi ?: 'Setoran modal operasional',
        ]);
    }

    /* ===== Target ===== */
    public function openTarget(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('modal-error', message: 'Anda tidak punya izin.');

            return;
        }
        $this->targetInput = number_format((int) Setting::get('modal_operasional_target', 0), 0, ',', '.');
        $this->resetErrorBag();
        $this->showTarget = true;
    }

    public function closeTarget(): void
    {
        $this->showTarget = false;
    }

    public function simpanTarget(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('modal-error', message: 'Anda tidak punya izin.');

            return;
        }
        Setting::set('modal_operasional_target', $this->toNumber($this->targetInput));
        $this->showTarget = false;
        $this->dispatch('modal-saved');
    }

    /** Top-up otomatis sebesar kekurangan menuju target. */
    public function isiKeTarget(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('modal-error', message: 'Anda tidak punya izin.');

            return;
        }

        $bulan = (int) ($this->bulan ?: now()->month);
        $tahun = (int) ($this->tahun ?: now()->year);

        $saldoAwal = $this->hitungSaldoAwal($bulan, $tahun);
        $setoranBulan = (float) Modal::totalOperasional($bulan, $tahun);
        $target = (float) Setting::get('modal_operasional_target', 0);

        // Kekurangan menuju target, memperhitungkan top-up yang sudah diisi bulan ini.
        $saran = $target - $saldoAwal - $setoranBulan;

        if ($saran <= 0) {
            $this->dispatch('modal-error', message: 'Dana sudah mencapai/melebihi target. Tidak perlu top-up.');

            return;
        }

        $tanggal = ($bulan === (int) now()->month && $tahun === (int) now()->year)
            ? now()->toDateString()
            : Carbon::create($tahun, $bulan, 1)->toDateString();

        $m = Modal::create([
            'tanggal' => $tanggal,
            'nominal' => $saran,
            'jenis' => 'operasional',
            'deskripsi' => 'Top-up otomatis ke target',
            'penginput_id' => auth()->id(),
        ]);
        $this->syncCashFlow($m);

        $this->resetPage();
        $this->dispatch('modal-saved');
    }

    /* ===== CRUD top-up ===== */
    public function openCreate(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('modal-error', message: 'Anda tidak punya izin menambah modal.');

            return;
        }

        $this->reset(['editingId', 'formNominal', 'formDeskripsi']);
        $this->resetErrorBag();
        $this->formTanggal = now()->toDateString();
        $this->showForm = true;
    }

    public function openEdit($id): void
    {
        if (! auth()->user()?->hasPermission('edit_modal')) {
            $this->dispatch('modal-error', message: 'Anda tidak punya izin mengubah modal.');

            return;
        }

        $m = Modal::operasional()->find($id);
        if (! $m) {
            $this->dispatch('modal-error', message: 'Data modal tidak ditemukan.');

            return;
        }

        $this->editingId = $m->id;
        $this->formTanggal = $m->tanggal->toDateString();
        $this->formNominal = number_format((int) $m->nominal, 0, ',', '.');
        $this->formDeskripsi = $m->deskripsi;
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $izin = $this->editingId ? 'edit_modal' : 'create_modal';
        if (! auth()->user()?->hasPermission($izin)) {
            $this->dispatch('modal-error', message: 'Anda tidak punya izin.');

            return;
        }

        $this->formNominal = (string) $this->toNumber($this->formNominal);

        $this->validate([
            'formTanggal' => ['required', 'date'],
            'formNominal' => ['required', 'numeric', 'min:1'],
            'formDeskripsi' => ['nullable', 'string', 'max:500'],
        ], [], [
            'formTanggal' => 'tanggal',
            'formNominal' => 'nominal',
            'formDeskripsi' => 'keterangan',
        ]);

        $data = [
            'tanggal' => $this->formTanggal,
            'nominal' => $this->toNumber($this->formNominal),
            'jenis' => 'operasional',
            'deskripsi' => $this->formDeskripsi,
        ];

        if ($this->editingId) {
            $m = Modal::operasional()->find($this->editingId);
            if (! $m) {
                $this->dispatch('modal-error', message: 'Data modal tidak ditemukan.');

                return;
            }
            $m->update($data);
        } else {
            $data['penginput_id'] = auth()->id();
            $m = Modal::create($data);
        }

        $this->syncCashFlow($m);

        $this->showForm = false;
        $this->reset(['editingId', 'formNominal', 'formDeskripsi']);
        $this->resetPage();
        $this->dispatch('modal-saved');
    }

    public function deleteModal($id): void
    {
        if (! auth()->user()?->hasPermission('delete_modal')) {
            $this->dispatch('modal-deleteError', message: 'Anda tidak punya izin menghapus modal.');

            return;
        }

        $m = Modal::operasional()->find($id);
        if (! $m) {
            $this->dispatch('modal-deleteError', message: 'Data modal tidak ditemukan.');

            return;
        }

        $m->delete(); // cash flow ikut terhapus (booted deleting)
        $this->dispatch('modal-deleted');
    }

    protected function daftarBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    protected function daftarTahun(): array
    {
        $now = (int) now()->year;

        return range($now, $now - 5);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $bulan = (int) ($this->bulan ?: now()->month);
        $tahun = (int) ($this->tahun ?: now()->year);

        $modals = Modal::operasional()
            ->with('penginput')
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($x) => $x->where('deskripsi', 'like', $term)->orWhere('nominal', 'like', $term));
            })
            ->when(! $this->search, fn ($q) => $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan))
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        $saldoAwal = $this->hitungSaldoAwal($bulan, $tahun);
        $setoranBulan = (float) Modal::totalOperasional($bulan, $tahun);
        $mulaiPeriode = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $akhirPeriode = Carbon::create($tahun, $bulan, 1)->addMonthNoOverflow()->startOfMonth()->toDateString();
        $terpakai = (float) $this->terpakaiOperasionalQuery()
            ->whereYear('tanggal_transaksi', $tahun)->whereMonth('tanggal_transaksi', $bulan)->sum('nominal')
            + $this->terpakaiPrivateRange($mulaiPeriode, $akhirPeriode);
        $danaTersedia = $saldoAwal + $setoranBulan;
        $sisa = $danaTersedia - $terpakai;

        $target = (float) Setting::get('modal_operasional_target', 0);
        $saranTopUp = max($target - $saldoAwal - $setoranBulan, 0);

        // ===== Rincian modal pembelian akun per produk (periode terpilih) =====
        $privateIds = \App\Models\Product::where('tipe_akun', 'private')->pluck('id')->all();
        $namaAll = \App\Models\Product::pluck('nama_akun', 'id');
        $akunPerProduk = [];

        // SHARING / non-private: total pembelian akun periode
        $sharingRows = Spending::where('jenis_pengeluaran', 'pembelian_akun')
            ->whereYear('tanggal_transaksi', $tahun)
            ->whereMonth('tanggal_transaksi', $bulan)
            ->when(! empty($privateIds), fn ($q) => $q->where(fn ($x) => $x->whereNull('product_id')->orWhereNotIn('product_id', $privateIds)))
            ->selectRaw('product_id, COALESCE(SUM(nominal), 0) as total')
            ->groupBy('product_id')
            ->get();
        foreach ($sharingRows as $r) {
            if ((float) $r->total <= 0) {
                continue;
            }
            $akunPerProduk[] = [
                'nama' => $r->product_id ? ($namaAll[$r->product_id] ?? 'Produk') : 'Tanpa Produk',
                'tipe' => 'sharing',
                'durasi' => null,
                'satuan' => null,
                'jumlah' => null,
                'total' => (float) $r->total,
            ];
        }

        // PRIVATE: modal satuan (harga BERLAKU s/d akhir periode) x jumlah order periode
        if (! empty($privateIds)) {
            $catalog = \App\Models\ProductModalPrice::whereIn('product_id', $privateIds)
                ->where('berlaku_mulai', '<', $akhirPeriode)
                ->orderBy('berlaku_mulai', 'desc')->orderBy('created_at', 'desc')
                ->get(['product_id', 'durasi_value', 'durasi_type', 'harga']);
            $unitMap = [];
            foreach ($catalog as $c) {
                $k = $c->product_id.'|'.$c->durasi_value.'|'.$c->durasi_type;
                if (! array_key_exists($k, $unitMap)) {
                    $unitMap[$k] = (float) $c->harga;
                }
            }

            $privOrders = \App\Models\OrderItem::query()
                ->whereHas('order', function ($q) use ($tahun, $bulan) {
                    $q->whereIn('status', ['paid', 'processing', 'completed'])
                        ->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$tahun])
                        ->whereRaw('MONTH(COALESCE(paid_at, created_at)) = ?', [$bulan]);
                })
                ->whereIn('order_items.product_id', $privateIds)
                ->selectRaw('order_items.product_id, order_items.duration_value, order_items.duration_type, COALESCE(SUM(order_items.quantity), 0) as qty')
                ->groupBy('order_items.product_id', 'order_items.duration_value', 'order_items.duration_type')
                ->get();
            $orderQty = [];
            foreach ($privOrders as $o) {
                $orderQty[$o->product_id.'|'.$o->duration_value.'|'.$o->duration_type] = (int) $o->qty;
            }

            // Hanya produk yang BENAR-BENAR ada order di periode ini (bukan carry-forward harga saja).
            foreach ($orderQty as $k => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                [$pid, $dv, $dt] = explode('|', $k);
                $satuan = $unitMap[$k] ?? 0;
                $akunPerProduk[] = [
                    'nama' => $namaAll[$pid] ?? 'Produk',
                    'tipe' => 'private',
                    'durasi' => $dv.' '.$dt,
                    'satuan' => (float) $satuan,
                    'jumlah' => (int) $qty,
                    'total' => (float) $satuan * $qty,
                ];
            }
        }

        usort($akunPerProduk, fn ($a, $b) => $b['total'] <=> $a['total']);
        $pembelianAkun = array_sum(array_column($akunPerProduk, 'total'));

        // Paginasi rincian per produk (in-memory)
        $akunTotal = count($akunPerProduk);
        $akunTotalPages = max(1, (int) ceil($akunTotal / $this->akunPerPage));
        $this->akunPage = min(max(1, (int) $this->akunPage), $akunTotalPages);
        $akunItems = array_slice($akunPerProduk, ($this->akunPage - 1) * $this->akunPerPage, $this->akunPerPage);

        return view('livewire.pages.admin.modal.modal-list', [
            'akunPerProduk' => $akunPerProduk,
            'akunItems' => $akunItems,
            'akunTotal' => $akunTotal,
            'akunTotalPages' => $akunTotalPages,
            'modals' => $modals,
            'saldoAwal' => $saldoAwal,
            'setoranBulan' => $setoranBulan,
            'terpakai' => $terpakai,
            'danaTersedia' => $danaTersedia,
            'sisa' => $sisa,
            'target' => $target,
            'saranTopUp' => $saranTopUp,
            'pembelianAkun' => $pembelianAkun,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }
}
