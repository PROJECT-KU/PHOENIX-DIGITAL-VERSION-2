<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Exports\PesanPelangganExport;
use App\Models\CustomerMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class CustomerMessageList extends Component
{
    use WithPagination;

    /** baru | berjalan | selesai | semua — tab yang sekaligus kartu hitungan. */
    #[Url(as: 'tab', except: 'baru')]
    public string $tab = 'baru';

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    // ===== Saringan lanjutan =====
    #[Url(as: 'status', except: '')]
    public string $fStatus = '';

    #[Url(as: 'prioritas', except: '')]
    public string $fPrioritas = '';

    #[Url(as: 'dari', except: '')]
    public string $fDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $fSampai = '';

    /** baru | lama | prioritas — 'prioritas' menaikkan yang paling mendesak. */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    #[Url(as: 'per', except: 12)]
    public int $perPage = 12;

    /** Id pesan yang dicentang untuk aksi massal. */
    public array $pilih = [];

    public const TAB = ['baru', 'berjalan', 'selesai', 'semua'];

    public const URUT = ['baru', 'lama', 'prioritas'];

    public const STATUS = [
        'open' => 'Terbuka',
        'pending' => 'Tertunda',
        'in_progress' => 'Diproses',
        'resolved' => 'Selesai',
        'closed' => 'Ditutup',
    ];

    public const PRIORITAS = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    public function updatingSearch(): void
    {
        $this->pilih = [];
        $this->resetPage();
    }

    public function updated($nama): void
    {
        if (in_array($nama, ['fStatus', 'fPrioritas', 'fDari', 'fSampai', 'urut', 'perPage'], true)) {
            $this->pilih = [];
            $this->resetPage();
        }

        if ($nama === 'urut' && ! in_array($this->urut, self::URUT, true)) {
            $this->urut = 'baru';
        }
    }

    public function setTab(string $t): void
    {
        $this->tab = in_array($t, self::TAB, true) ? $t : 'baru';
        $this->pilih = [];
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'fStatus', 'fPrioritas', 'fDari', 'fSampai', 'pilih']);
        $this->resetPage();
    }

    public function lepasSaring(string $nama): void
    {
        if (in_array($nama, ['fStatus', 'fPrioritas', 'fDari', 'fSampai', 'search'], true)) {
            $this->$nama = '';
            $this->pilih = [];
            $this->resetPage();
        }
    }

    public function getAdaSaringProperty(): bool
    {
        return filled($this->fStatus) || filled($this->fPrioritas) || filled($this->fDari) || filled($this->fSampai);
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
        if ($this->fStatus !== '') {
            $tambah('fStatus', self::STATUS[$this->fStatus] ?? $this->fStatus);
        }
        if ($this->fPrioritas !== '') {
            $tambah('fPrioritas', 'Prioritas '.(self::PRIORITAS[$this->fPrioritas] ?? $this->fPrioritas));
        }
        if ($this->fDari !== '') {
            $tambah('fDari', 'Dari '.$this->fDari);
        }
        if ($this->fSampai !== '') {
            $tambah('fSampai', 'Sampai '.$this->fSampai);
        }

        return $chip;
    }

    // ===== Tindakan satuan =====

    protected function bolehUbah(): bool
    {
        if (auth()->user()?->hasPermission('edit_customer_message') || auth()->user()?->hasPermission('view_customer_message')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah pesan pelanggan.');

        return false;
    }

    public function updateStatus($id, $value): void
    {
        if (! $this->bolehUbah() || ! array_key_exists($value, self::STATUS)) {
            return;
        }

        CustomerMessage::whereKey($id)->update(['status' => $value]);
        $this->dispatch('swal-success', message: 'Status diperbarui jadi '.self::STATUS[$value].'.');
        $this->dispatch('sidebar-badge-updated');
    }

    public function updatePriority($id, $value): void
    {
        if (! $this->bolehUbah() || ! array_key_exists($value, self::PRIORITAS)) {
            return;
        }

        CustomerMessage::whereKey($id)->update(['priority' => $value]);
        $this->dispatch('swal-success', message: 'Prioritas diperbarui jadi '.self::PRIORITAS[$value].'.');
    }

    /** Tandai satu pesan sudah dibaca tanpa membuka halamannya. */
    public function tandaiDibaca($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        CustomerMessage::find($id)?->markAsRead();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Ditandai sudah dibaca.');
    }

    public function delete($id): void
    {
        if (! auth()->user()->hasPermission('delete_customer_message')) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Anda tidak memiliki izin menghapus pesan pelanggan.');

            return;
        }

        $customerMessage = CustomerMessage::find($id);

        if (! $customerMessage) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Data Pesan Pelanggan tidak ditemukan!');

            return;
        }

        // Pesan yang belum dibaca tidak boleh hilang sebelum ada yang melihatnya.
        if ($customerMessage->belumDibaca()) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Pesan belum dibaca dan tidak bisa dihapus!');

            return;
        }

        $customerMessage->delete();

        $this->dispatch('CustomerMessage-deleted', id: $id);
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

    public function tandaiDibacaTerpilih(): void
    {
        if (! $this->bolehUbah() || $this->pilih === []) {
            return;
        }

        $jumlah = CustomerMessage::whereKey($this->pilih)->unread()->update(['read_at' => now()]);
        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan ditandai sudah dibaca.');
    }

    public function statusTerpilih(string $value): void
    {
        if (! $this->bolehUbah() || $this->pilih === [] || ! array_key_exists($value, self::STATUS)) {
            return;
        }

        $jumlah = CustomerMessage::whereKey($this->pilih)->update(['status' => $value]);
        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan diubah jadi '.self::STATUS[$value].'.');
    }

    public function hapusTerpilih(): void
    {
        if (! auth()->user()?->hasPermission('delete_customer_message') || $this->pilih === []) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus pesan pelanggan.');

            return;
        }

        // Pesan yang belum dibaca tetap dilindungi, sama seperti hapus satuan.
        $jumlah = CustomerMessage::whereKey($this->pilih)->read()->delete();
        $lewat = count($this->pilih) - $jumlah;

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan dihapus.'
            .($lewat ? ' '.$lewat.' dilewati karena belum dibaca.' : ''));
    }

    // ===== Unduhan =====

    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('view_customer_message'), 403);

        return Excel::download(new PesanPelangganExport($this->dataEkspor()), 'pesan-pelanggan-'.now()->format('Ymd-His').'.xlsx');
    }

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_customer_message'), 403);

        $data = $this->dataEkspor();

        $pdf = Pdf::loadView('exports.pesan-pelanggan-pdf', [
            'pesan' => $data,
            'saringan' => $this->pilih
                ? ['hanya '.count($this->pilih).' pesan terpilih']
                : collect($this->chipSaring)->pluck('label')->all(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), 'pesan-pelanggan-'.now()->format('Ymd-His').'.pdf');
    }

    /** Yang DICENTANG bila ada, kalau tidak ikut saringan yang sedang tampil. */
    protected function dataEkspor()
    {
        return $this->pilih
            ? CustomerMessage::whereKey($this->pilih)->get()
            : $this->kueri()->get();
    }

    /** Kueri daftar — dipakai kartu, ekspor, dan hitungan. */
    protected function kueri()
    {
        return CustomerMessage::query()
            ->when($this->tab === 'baru', fn ($q) => $q->unread())
            ->when($this->tab === 'berjalan', fn ($q) => $q->berjalan())
            ->when($this->tab === 'selesai', fn ($q) => $q->whereIn('status', ['resolved', 'closed']))
            ->when($this->fStatus !== '', fn ($q) => $q->where('status', $this->fStatus))
            ->when($this->fPrioritas !== '', fn ($q) => $q->where('priority', $this->fPrioritas))
            ->when($this->fDari !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->fDari))
            ->when($this->fSampai !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->fSampai))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                // Nomor ikut dicari lewat bentuk INTI-nya: nomor yang sama bisa
                // tersimpan "0895…" atau "62895…". Hanya bila yang diketik
                // memang berbentuk nomor — kalau tidak, angka nyasar di dalam
                // kode tiket (TKT-5A2B-…) ikut mencocokkan nomor orang lain.
                $inti = preg_match('/^[\d\s().+-]{4,}$/', trim($this->search))
                    ? \App\Models\Customer::normalisasiNoHp($this->search)
                    : '';

                $q->where(function ($sub) use ($term, $inti) {
                    $sub->where('ticket', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('message', 'like', $term);

                    if ($inti !== '') {
                        $sub->orWhere('no_telp', 'like', '%'.$inti.'%');
                    }
                });
            })
            // Mendesak lebih dulu, lalu yang paling lama menunggu.
            // CASE, bukan FIELD(): FIELD() hanya ada di MySQL, sedangkan uji
            // berjalan di SQLite — kueri semacam itu hijau di uji lalu galat
            // di server, atau sebaliknya.
            ->when($this->urut === 'prioritas', fn ($q) => $q->orderByRaw(
                "CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END"
            )->orderBy('created_at'))
            ->when($this->urut === 'lama', fn ($q) => $q->orderBy('created_at'))
            ->when($this->urut === 'baru', fn ($q) => $q->orderByDesc('created_at'));
    }

    public function render()
    {
        $messages = $this->kueri()->paginate(max(6, min(48, $this->perPage)));

        // Satu kueri untuk hitungan status; sisanya dihitung dari situ.
        $perStatus = CustomerMessage::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $selesai = (int) ($perStatus['resolved'] ?? 0) + (int) ($perStatus['closed'] ?? 0);

        $tabCounts = [
            'baru' => CustomerMessage::unread()->count(),
            'berjalan' => (int) $perStatus->sum() - $selesai,
            'selesai' => $selesai,
            'semua' => (int) $perStatus->sum(),
        ];

        return view('livewire.pages.admin.message.customer-message-list', [
            'messages' => $messages,
            'tabCounts' => $tabCounts,
            'unreadCount' => $tabCounts['baru'],
            // Tiket mendesak yang belum selesai — yang paling pantas dikerjakan dulu.
            'mendesak' => CustomerMessage::berjalan()->whereIn('priority', ['urgent', 'high'])->count(),
            // Tiket terlama yang masih menunggu dibaca, untuk kartu ringkasan.
            'tertua' => CustomerMessage::unread()->oldest()->first(),
        ])
            ->layout('livewire.layout.templateindex');
    }
}
