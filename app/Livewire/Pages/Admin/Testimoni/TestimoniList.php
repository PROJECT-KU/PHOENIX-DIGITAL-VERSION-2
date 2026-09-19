<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Exports\TestimoniExport;
use App\Models\Testimoni;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class TestimoniList extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public $searchTestimoni = '';

    // Tab moderasi aktif: pending (menunggu) | active (disetujui) | non-active (ditolak) | all
    #[Url(as: 'status', except: 'pending')]
    public string $filter = 'pending';

    /** Testimoni yang sedang dibuka di jendela detail. */
    public ?string $lihatId = null;

    // ===== Saringan lanjutan =====
    #[Url(as: 'bintang', except: '')]
    public string $fRating = '';

    /** '' | customer (kiriman pelanggan) | admin (diinput admin) */
    #[Url(as: 'sumber', except: '')]
    public string $fSumber = '';

    /** '' | ya (tertaut pelanggan) | tidak */
    #[Url(as: 'pembeli', except: '')]
    public string $fVerifikasi = '';

    /** '' | ya | tidak */
    #[Url(as: 'anonim', except: '')]
    public string $fAnonim = '';

    #[Url(as: 'dari', except: '')]
    public string $fDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $fSampai = '';

    /** baru | lama | tinggi | rendah */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    #[Url(as: 'per', except: 12)]
    public int $perHalaman = 12;

    /** Menampilkan arsip (yang sudah dihapus) alih-alih data aktif. */
    #[Url(as: 'arsip', except: false)]
    public bool $arsip = false;

    /** Id testimoni yang dicentang untuk aksi massal. */
    public array $pilih = [];

    /** Testimoni yang sedang ditanyakan alasan penolakannya. */
    public ?string $tolakId = null;

    public string $tolakAlasan = '';

    public const URUT = ['baru', 'lama', 'tinggi', 'rendah'];

    public const ALASAN_TOLAK = [
        'Berisi promosi/tautan',
        'Tidak sesuai pengalaman pembelian',
        'Kata-kata tidak pantas',
        'Terindikasi kiriman palsu',
        'Kiriman ganda',
    ];

    /**
     * Rute admin.testimoni.show (/admin/DataTestimoni/{id}) membuka halaman ini
     * dengan jendela detail. Dulu rute itu memuat HALAMAN UBAH hanya dengan
     * izin melihat — dan simpannya tidak memeriksa izin.
     */
    public function mount($testimoni = null): void
    {
        if ($testimoni) {
            $this->lihatId = Testimoni::whereKey($testimoni)->value('id');
            $this->filter = 'all';
        }
    }

    public function lihat(string $id): void
    {
        $this->lihatId = Testimoni::whereKey($id)->value('id');
    }

    public function tutupLihat(): void
    {
        $this->lihatId = null;
    }

    public function updatedSearchTestimoni()
    {
        $this->resetPage();
    }

    /** Setiap saringan/urutan berubah, kembali ke halaman 1 & lepas centang. */
    public function updated($nama): void
    {
        if (in_array($nama, ['fRating', 'fSumber', 'fVerifikasi', 'fAnonim', 'fDari', 'fSampai', 'urut', 'perHalaman', 'arsip'], true)) {
            $this->pilih = [];
            $this->resetPage();
        }

        if ($nama === 'urut' && ! in_array($this->urut, self::URUT, true)) {
            $this->urut = 'baru';
        }
    }

    public function resetSaring(): void
    {
        $this->reset(['fRating', 'fSumber', 'fVerifikasi', 'fAnonim', 'fDari', 'fSampai', 'searchTestimoni', 'pilih']);
        $this->resetPage();
    }

    /** Ada saringan lanjutan yang aktif? (untuk lencana di tombol Saring) */
    public function getAdaSaringProperty(): bool
    {
        return filled($this->fRating) || filled($this->fSumber) || filled($this->fVerifikasi)
            || filled($this->fAnonim) || filled($this->fDari) || filled($this->fSampai);
    }

    public function setFilter(string $f): void
    {
        $this->filter = in_array($f, ['pending', 'active', 'non-active', 'all'], true) ? $f : 'pending';
        // Memilih tab status selalu keluar dari arsip — kalau tidak, tabnya
        // tampak berpindah tapi isinya tetap data terhapus.
        $this->arsip = false;
        $this->pilih = [];
        $this->resetPage();
    }

    /** Izin moderasi; sekaligus memberi pesan yang jelas bila tidak punya. */
    protected function bolehModerasi(): bool
    {
        if (auth()->user()?->hasPermission('edit_testimoni')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin memoderasi testimoni.');

        return false;
    }

    /** Catatan siapa & kapan meninjau — dipakai approve/reject/massal. */
    protected function jejakTinjau(array $tambahan = []): array
    {
        return array_merge(['ditinjau_at' => now(), 'ditinjau_oleh' => auth()->id()], $tambahan);
    }

    /**
     * Setujui testimoni -> tampil di publik (status active).
     * Logika auto-member dipertahankan persis seperti sebelumnya.
     */
    public function approve($id)
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $testimoni = Testimoni::find($id);
        if (! $testimoni) {
            $this->dispatch('swal-error', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        $testimoni->update($this->jejakTinjau(['status' => 'active', 'alasan_tolak' => null]));
        $this->dispatch('sidebar-badge-updated');

        // Pengirim jadi member otomatis bila nomornya cocok pelanggan yang
        // punya pesanan selesai. Menyetujui testimoni tamu/admin tidak
        // mengaktifkan siapa pun (customer null).
        if ($testimoni->customer && $testimoni->customer->aktifkanMember()) {
            $this->dispatch('swal-success', message: $testimoni->customer->nama.' disetujui & otomatis jadi Member 🎉');

            return;
        }

        // Bintang rendah tetap disetujui, tapi jujur bahwa beranda tidak
        // menampilkannya — tanpa ini admin mengira testimoninya sudah tayang.
        if ($testimoni->tersembunyiKarenaRating()) {
            $this->dispatch('swal-success', message: 'Disetujui, tapi bintang '.$testimoni->rating.' tidak tampil di beranda. Pakai Sorot bila ingin ditampilkan.');

            return;
        }

        $this->dispatch('swal-success', message: 'Testimoni disetujui & kini tampil di publik.');
    }

    /** Buka tanya-alasan sebelum menolak. */
    public function bukaTolak(string $id): void
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        // 'massal' = menolak semua yang dicentang; selain itu satu testimoni.
        $this->tolakId = $id === 'massal' ? 'massal' : Testimoni::whereKey($id)->value('id');
        $this->tolakAlasan = '';
    }

    public function tutupTolak(): void
    {
        $this->tolakId = null;
        $this->tolakAlasan = '';
    }

    /**
     * Tolak testimoni -> disembunyikan dari publik (status non-active),
     * lengkap dengan alasannya supaya keputusan lama bisa ditelusuri.
     */
    public function reject($id = null)
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $testimoni = Testimoni::find($id ?? $this->tolakId);
        if (! $testimoni) {
            $this->dispatch('swal-error', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        // Menolak TIDAK mencabut keanggotaan yang sudah terlanjur diberikan —
        // poin & kode referral tetap hak pelanggan.
        $testimoni->update($this->jejakTinjau([
            'status' => 'non-active',
            'sorot' => false,
            'alasan_tolak' => trim($this->tolakAlasan) ?: null,
        ]));

        $this->tutupTolak();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Testimoni ditolak & disembunyikan dari publik.');
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

    /** Setujui semua yang dicentang. Auto-member tetap berjalan per pengirim. */
    public function setujuiTerpilih(): void
    {
        if (! $this->bolehModerasi() || $this->pilih === []) {
            return;
        }

        $daftar = Testimoni::with('customer')->whereKey($this->pilih)->get();
        $member = 0;
        foreach ($daftar as $t) {
            $t->update($this->jejakTinjau(['status' => 'active', 'alasan_tolak' => null]));
            if ($t->customer && $t->customer->aktifkanMember()) {
                $member++;
            }
        }

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $daftar->count().' testimoni disetujui'.($member ? " & {$member} pengirim jadi Member 🎉" : '.'));
    }

    /** Tolak semua yang dicentang, dengan satu alasan yang sama. */
    public function tolakTerpilih(): void
    {
        if (! $this->bolehModerasi() || $this->pilih === []) {
            return;
        }

        $jumlah = Testimoni::whereKey($this->pilih)->get()
            ->each(fn ($t) => $t->update($this->jejakTinjau([
                'status' => 'non-active',
                'sorot' => false,
                'alasan_tolak' => trim($this->tolakAlasan) ?: null,
            ])))
            ->count();

        $this->pilih = [];
        $this->tutupTolak();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' testimoni ditolak & disembunyikan.');
    }

    // ===== Kendali tampil di beranda =====

    /** Sorot: naikkan ke barisan depan beranda (dan tampilkan walau bintangnya rendah). */
    public function alihSorot(string $id): void
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $t = Testimoni::find($id);
        if (! $t) {
            return;
        }

        $t->update(['sorot' => ! $t->sorot]);

        $this->dispatch('swal-success', message: $t->sorot
            ? 'Disorot — tampil di barisan depan beranda.'
            : 'Sorotan dilepas.');
    }

    /**
     * Geser urutan tampil satu posisi. Urutan dinormalkan dulu jadi 1..n
     * supaya nilai kembar/berlubang dari data lama tidak membuat tombolnya
     * tampak tidak bekerja (pola yang sama dipakai Data Banner).
     */
    public function geser(string $id, string $arah): void
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $ids = Testimoni::tampilPublik()->urutTampil()->pluck('id')->values();
        $i = $ids->search($id);
        $j = $arah === 'naik' ? $i - 1 : $i + 1;
        if ($i === false || $j < 0 || $j >= $ids->count()) {
            return;
        }

        $baru = $ids->all();
        [$baru[$i], $baru[$j]] = [$baru[$j], $baru[$i]];
        foreach ($baru as $posisi => $tid) {
            Testimoni::whereKey($tid)->update(['urutan' => $posisi + 1]);
        }
    }

    // ===== Arsip =====

    public function deleteTestimoni($id)
    {
        if (! auth()->user()->hasPermission('delete_testimoni')) {
            $this->dispatch('testimoni-deleteError', message: 'Anda tidak memiliki izin menghapus testimoni.');

            return;
        }

        $testimoni = Testimoni::find($id);

        if (! $testimoni) {
            $this->dispatch('testimoni-deleteError', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        // Arsip dulu (soft delete): fotonya BARU dihapus saat dibuang permanen,
        // supaya pemulihan tidak menghasilkan testimoni tanpa foto.
        $testimoni->delete();
        $this->lihatId = null;

        $this->dispatch('testimoni-deleted', id: $id);
        // Menghapus kiriman pelanggan yang belum ditinjau juga mengurangi badge.
        $this->dispatch('sidebar-badge-updated');
    }

    /** Kembalikan dari arsip. */
    public function pulihkan(string $id): void
    {
        if (! auth()->user()?->hasPermission('delete_testimoni')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin memulihkan testimoni.');

            return;
        }

        Testimoni::onlyTrashed()->whereKey($id)->restore();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Testimoni dikembalikan dari arsip.');
    }

    /** Buang permanen — di sinilah fotonya ikut dihapus dari disk. */
    public function buangPermanen(string $id): void
    {
        if (! auth()->user()?->hasPermission('delete_testimoni')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus testimoni.');

            return;
        }

        $testimoni = Testimoni::onlyTrashed()->find($id);
        if (! $testimoni) {
            return;
        }

        if ($testimoni->foto) {
            $filePath = storage_path('app/public/img/testimoni/'.$testimoni->foto);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $testimoni->forceDelete();
        $this->lihatId = null;
        $this->dispatch('swal-success', message: 'Testimoni dibuang permanen.');
    }

    // ===== Jendela detail =====

    /** Pindah ke tetangga di daftar yang SEDANG tampil, tanpa menutup jendela. */
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

        $tujuan = $ids->get($i + $langkah);
        if ($tujuan) {
            $this->lihatId = $tujuan;
        }
    }

    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('view_testimoni'), 403);

        $data = $this->kueri()->with('peninjau')->get();

        return Excel::download(new TestimoniExport($data), 'testimoni-'.now()->format('Ymd-His').'.xlsx');
    }

    /** Kueri daftar (saringan + urutan) — dipakai tabel, ekspor, dan navigasi detail. */
    protected function kueri()
    {
        $urutan = match ($this->urut) {
            'lama' => ['created_at', 'asc'],
            'tinggi' => ['rating', 'desc'],
            'rendah' => ['rating', 'asc'],
            default => ['created_at', 'desc'],
        };

        return Testimoni::query()
            ->when($this->arsip, fn ($q) => $q->onlyTrashed())
            // Di arsip, tab status TIDAK ikut menyaring: isinya campur semua
            // status, jadi menyaring lagi membuat arsip tampak kosong padahal ada.
            ->when(! $this->arsip && $this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->when($this->fRating !== '', fn ($q) => $q->where('rating', (int) $this->fRating))
            ->when($this->fSumber !== '', fn ($q) => $this->fSumber === 'customer'
                ? $q->where('source', 'customer')
                : $q->where(fn ($s) => $s->where('source', '!=', 'customer')->orWhereNull('source')))
            ->when($this->fVerifikasi !== '', fn ($q) => $this->fVerifikasi === 'ya'
                ? $q->whereNotNull('customer_id')
                : $q->whereNull('customer_id'))
            ->when($this->fAnonim !== '', fn ($q) => $q->where('anonim', $this->fAnonim === 'ya'))
            ->when($this->fDari !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->fDari))
            ->when($this->fSampai !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->fSampai))
            ->when($this->searchTestimoni !== '', function ($q) {
                $term = "%{$this->searchTestimoni}%";
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('peran', 'like', $term)
                        ->orWhere('pesan', 'like', $term);
                });
            })
            // Yang disorot selalu di atas, sisanya menurut pilihan pengurutan.
            ->orderByDesc('sorot')
            ->orderBy($urutan[0], $urutan[1]);
    }

    public function render()
    {
        // customer + hitungan pesanan selesai dimuat sekalian (1 query) supaya
        // admin bisa menilai keaslian testimoni tanpa membuka halaman lain.
        $pelangganHitung = fn ($q) => $q->withCount([
            'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
        ]);

        $Testimoni = $this->kueri()
            ->with(['customer' => $pelangganHitung])
            ->paginate(max(6, min(48, $this->perHalaman)));

        $tabCounts = [
            'all' => Testimoni::count(),
            'pending' => Testimoni::where('status', 'pending')->count(),
            'active' => Testimoni::where('status', 'active')->count(),
            'non-active' => Testimoni::where('status', 'non-active')->count(),
            'arsip' => Testimoni::onlyTrashed()->count(),
        ];

        // Sebaran bintang dari yang disetujui — sumber angka rata-rata.
        $sebaran = Testimoni::where('status', 'active')
            ->selectRaw('rating, count(*) as jumlah')
            ->groupBy('rating')->pluck('jumlah', 'rating');
        $totalSebaran = max(1, (int) $sebaran->sum());

        return view('livewire.pages.admin.testimoni.testimoni-list', [
            'Testimoni' => $Testimoni,
            'tabCounts' => $tabCounts,
            // Rata-rata rating yang TAMPIL di publik (hanya yang disetujui).
            'rataRating' => round((float) Testimoni::where('status', 'active')->avg('rating'), 1),
            'sebaran' => collect(range(5, 1))->mapWithKeys(fn ($b) => [$b => [
                'jumlah' => (int) ($sebaran[$b] ?? 0),
                'persen' => round(((int) ($sebaran[$b] ?? 0)) / $totalSebaran * 100),
            ]]),
            'jumlahTampil' => Testimoni::tampilPublik()->count(),
            // Posisi di beranda (1 = kartu pertama). Yang di luar BERANDA_MAKS
            // tidak pernah dilihat pengunjung — itulah gunanya Sorot.
            'nomorTampil' => Testimoni::tampilPublik()->urutTampil()->pluck('id')->flip()->map(fn ($i) => $i + 1),
            'detail' => $this->lihatId
                ? Testimoni::withTrashed()->with(['customer' => $pelangganHitung, 'peninjau'])->find($this->lihatId)
                : null,
            'tolak' => $this->tolakId && $this->tolakId !== 'massal' ? Testimoni::find($this->tolakId) : null,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
