<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Exports\TestimoniExport;
use App\Models\Setting;
use App\Models\Testimoni;
use App\Support\RiwayatTestimoni;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /** Jendela pratinjau urutan slider beranda. */
    public bool $pratinjauBeranda = false;

    /** Jendela aktivitas moderasi (jejak lintas testimoni). */
    public bool $lihatAktivitas = false;

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

    /** '' | ya (disorot) | tidak */
    #[Url(as: 'sorot', except: '')]
    public string $fSorot = '';

    /** '' | ya (tampil di beranda) | tidak */
    #[Url(as: 'tayang', except: '')]
    public string $fBeranda = '';

    #[Url(as: 'dari', except: '')]
    public string $fDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $fSampai = '';

    /** baru | lama | tunggu | tinggi | rendah */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    /** kartu | daftar — daftar memadatkan banyak kiriman dalam satu layar. */
    #[Url(as: 'tampilan', except: 'kartu')]
    public string $tampilan = 'kartu';

    /** Banyak kartu testimoni di beranda (tersimpan di tabel settings). */
    public int $jumlahBeranda = 9;

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

    public const URUT = ['baru', 'lama', 'tunggu', 'tinggi', 'rendah', 'nama'];

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
        $this->jumlahBeranda = Testimoni::jumlahBeranda();

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
        if (in_array($nama, ['fRating', 'fSumber', 'fVerifikasi', 'fAnonim', 'fSorot', 'fBeranda', 'fDari', 'fSampai', 'urut', 'perHalaman', 'arsip'], true)) {
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

    /** Ubah banyak kartu testimoni yang tampil di beranda. */
    public function updatedJumlahBeranda(): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_testimoni'), 403);

        $this->jumlahBeranda = max(3, min(24, (int) $this->jumlahBeranda));
        Setting::set(Testimoni::SETELAN_BERANDA, $this->jumlahBeranda);

        $this->dispatch('swal-success', message: 'Beranda kini memuat '.$this->jumlahBeranda.' testimoni.');
    }

    public function resetSaring(): void
    {
        $this->reset(['fRating', 'fSumber', 'fVerifikasi', 'fAnonim', 'fSorot', 'fBeranda', 'fDari', 'fSampai', 'searchTestimoni', 'pilih']);
        $this->resetPage();
    }

    /** Lepas satu saringan lewat chip-nya. */
    public function lepasSaring(string $nama): void
    {
        if (in_array($nama, ['fRating', 'fSumber', 'fVerifikasi', 'fAnonim', 'fSorot', 'fBeranda', 'fDari', 'fSampai', 'searchTestimoni'], true)) {
            $this->$nama = '';
            $this->pilih = [];
            $this->resetPage();
        }
    }

    /**
     * Saringan yang sedang aktif, sebagai chip yang bisa dilepas satu-satu.
     * Tanpa ini saringan aktif tidak terlihat begitu panel Saring ditutup.
     */
    public function getChipSaringProperty(): array
    {
        $chip = [];
        // &$chip: arrow function menyalin nilai, jadi tanpa acuan ini
        // penambahannya hilang dan chipnya selalu kosong.
        $tambah = function ($nama, $label) use (&$chip) {
            $chip[] = ['nama' => $nama, 'label' => $label];
        };

        if ($this->searchTestimoni !== '') {
            $tambah('searchTestimoni', 'Cari: "'.$this->searchTestimoni.'"');
        }
        if ($this->fRating !== '') {
            $tambah('fRating', $this->fRating.' bintang');
        }
        if ($this->fSumber !== '') {
            $tambah('fSumber', $this->fSumber === 'customer' ? 'Kiriman pelanggan' : 'Diinput admin');
        }
        if ($this->fVerifikasi !== '') {
            $tambah('fVerifikasi', $this->fVerifikasi === 'ya' ? 'Tertaut pelanggan' : 'Tidak tertaut');
        }
        if ($this->fAnonim !== '') {
            $tambah('fAnonim', $this->fAnonim === 'ya' ? 'Anonim' : 'Nama tampil');
        }
        if ($this->fSorot !== '') {
            $tambah('fSorot', $this->fSorot === 'ya' ? 'Disorot' : 'Tanpa sorot');
        }
        if ($this->fBeranda !== '') {
            $tambah('fBeranda', $this->fBeranda === 'ya' ? 'Tampil di beranda' : 'Tidak tampil di beranda');
        }
        if ($this->fDari !== '') {
            $tambah('fDari', 'Dari '.$this->fDari);
        }
        if ($this->fSampai !== '') {
            $tambah('fSampai', 'Sampai '.$this->fSampai);
        }

        return $chip;
    }

    /** Ada saringan lanjutan yang aktif? (untuk lencana di tombol Saring) */
    public function getAdaSaringProperty(): bool
    {
        return filled($this->fRating) || filled($this->fSumber) || filled($this->fVerifikasi)
            || filled($this->fAnonim) || filled($this->fSorot) || filled($this->fBeranda)
            || filled($this->fDari) || filled($this->fSampai);
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
        RiwayatTestimoni::catat($testimoni, 'disetujui');
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
        RiwayatTestimoni::catat($testimoni, 'ditolak', trim($this->tolakAlasan) ?: null);

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
            RiwayatTestimoni::catat($t, 'disetujui', 'Lewat aksi massal');
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

        $alasan = trim($this->tolakAlasan) ?: null;
        $jumlah = Testimoni::whereKey($this->pilih)->get()
            ->each(function ($t) use ($alasan) {
                $t->update($this->jejakTinjau(['status' => 'non-active', 'sorot' => false, 'alasan_tolak' => $alasan]));
                RiwayatTestimoni::catat($t, 'ditolak', $alasan ? $alasan.' (massal)' : 'Lewat aksi massal');
            })
            ->count();

        $this->pilih = [];
        $this->tutupTolak();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' testimoni ditolak & disembunyikan.');
    }

    // ===== Kendali tampil di beranda =====

    /**
     * Tandai sudah/belum dihubungi. Tombol Balas WhatsApp tidak meninggalkan
     * jejak, jadi pengirim yang sama gampang diucapkan terima kasih dua kali.
     */
    public function alihDihubungi(string $id): void
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $t = Testimoni::find($id);
        if (! $t) {
            return;
        }

        $sudah = $t->sudahDihubungi();
        $t->update([
            'dihubungi_at' => $sudah ? null : now(),
            'dihubungi_oleh' => $sudah ? null : auth()->id(),
        ]);

        $this->dispatch('swal-success', message: $sudah ? 'Penanda dihubungi dilepas.' : 'Ditandai sudah dihubungi.');
    }

    /**
     * Naikkan ke urutan teratas beranda. Tombol ↑ hanya menggeser satu posisi,
     * jadi testimoni yang jauh di bawah butuh puluhan klik untuk sampai atas.
     */
    public function naikkanKeAtas(string $id): void
    {
        if (! $this->bolehModerasi()) {
            return;
        }

        $ids = Testimoni::tampilPublik()->urutTampil()->pluck('id')->all();
        $i = array_search($id, $ids, true);
        if ($i === false) {
            return;
        }

        array_splice($ids, $i, 1);
        array_unshift($ids, $id);
        foreach ($ids as $posisi => $tid) {
            Testimoni::whereKey($tid)->update(['urutan' => $posisi + 1]);
        }

        $this->dispatch('swal-success', message: 'Dinaikkan ke urutan pertama beranda.');
    }

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
        RiwayatTestimoni::catat($t, $t->sorot ? 'disorot' : 'sorot-dilepas');

        if (! $t->sorot) {
            $this->dispatch('swal-success', message: 'Sorotan dilepas.');

            return;
        }

        // Menyorot lebih banyak daripada kapasitas beranda tidak menaikkan
        // apa pun — yang kelebihan tetap tidak terlihat pengunjung.
        $disorot = Testimoni::tampilPublik()->where('sorot', true)->count();
        $muat = Testimoni::jumlahBeranda();

        $this->dispatch('swal-success', message: $disorot > $muat
            ? 'Disorot, tapi sudah ada '.$disorot.' testimoni disorot sedangkan beranda cuma memuat '.$muat.'. Lepas sebagian sorotan.'
            : 'Disorot — tampil di barisan depan beranda.');
    }

    /** Sorot / lepas sorot semua yang dicentang sekaligus. */
    public function sorotTerpilih(bool $nyala): void
    {
        if (! $this->bolehModerasi() || $this->pilih === []) {
            return;
        }

        $daftar = Testimoni::whereKey($this->pilih)->where('status', 'active')->get();
        foreach ($daftar as $t) {
            $t->update(['sorot' => $nyala]);
            RiwayatTestimoni::catat($t, $nyala ? 'disorot' : 'sorot-dilepas', 'Lewat aksi massal');
        }

        $lewat = count($this->pilih) - $daftar->count();
        $this->pilih = [];
        $this->dispatch('swal-success', message: $daftar->count().($nyala ? ' testimoni disorot.' : ' sorotan dilepas.')
            .($lewat ? ' '.$lewat.' dilewati karena belum disetujui.' : ''));
    }

    /** Arsipkan semua yang dicentang. */
    public function arsipkanTerpilih(): void
    {
        if (! auth()->user()?->hasPermission('delete_testimoni') || $this->pilih === []) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus testimoni.');

            return;
        }

        $jumlah = Testimoni::whereKey($this->pilih)->get()
            ->each(function ($t) {
                RiwayatTestimoni::catat($t, 'diarsipkan', 'Lewat aksi massal');
                $t->delete();
            })
            ->count();

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' testimoni dipindahkan ke Arsip.');
    }

    /** Pulihkan semua yang dicentang (dipakai di tampilan Arsip). */
    public function pulihkanTerpilih(): void
    {
        if (! auth()->user()?->hasPermission('delete_testimoni') || $this->pilih === []) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin memulihkan testimoni.');

            return;
        }

        $daftar = Testimoni::onlyTrashed()->whereKey($this->pilih)->get();
        foreach ($daftar as $t) {
            $t->restore();
            RiwayatTestimoni::catat($t, 'dipulihkan', 'Lewat aksi massal');
        }

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $daftar->count().' testimoni dikembalikan dari arsip.');
    }

    /** Buang permanen semua yang dicentang, berikut fotonya. */
    public function buangTerpilih(): void
    {
        if (! auth()->user()?->hasPermission('delete_testimoni') || $this->pilih === []) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus testimoni.');

            return;
        }

        $daftar = Testimoni::onlyTrashed()->whereKey($this->pilih)->get();
        foreach ($daftar as $t) {
            $this->hapusFoto($t);
            $t->forceDelete();
        }

        $this->pilih = [];
        $this->dispatch('swal-success', message: $daftar->count().' testimoni dibuang permanen.');
    }

    /** Hapus berkas foto dari disk (dipakai saat dibuang permanen). */
    protected function hapusFoto(Testimoni $testimoni): void
    {
        if (! $testimoni->foto) {
            return;
        }

        $filePath = storage_path('app/public/img/testimoni/'.$testimoni->foto);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
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
        RiwayatTestimoni::catat($testimoni, 'diarsipkan');
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

        $testimoni = Testimoni::onlyTrashed()->find($id);
        if ($testimoni) {
            $testimoni->restore();
            RiwayatTestimoni::catat($testimoni, 'dipulihkan');
        }

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

        $this->hapusFoto($testimoni);
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

        $data = $this->dataEkspor();

        return Excel::download(new TestimoniExport($data), 'testimoni-'.now()->format('Ymd-His').'.xlsx');
    }

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_testimoni'), 403);

        $data = $this->dataEkspor();

        // Nomor WhatsApp sengaja tidak ikut: berkas laporan sering dibagikan.
        $pdf = Pdf::loadView('exports.testimoni-pdf', [
            'testimoni' => $data,
            'judul' => $this->arsip ? 'Arsip Testimoni' : 'Data Testimoni',
            'saringan' => $this->pilih
                ? ['hanya '.count($this->pilih).' testimoni terpilih']
                : collect($this->chipSaring)->pluck('label')->all(),
            'rata' => round((float) $data->avg('rating'), 1),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), 'testimoni-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * Isi berkas ekspor: yang DICENTANG bila ada, kalau tidak ikut saringan
     * yang sedang tampil.
     */
    protected function dataEkspor()
    {
        return $this->pilih
            ? Testimoni::withTrashed()->with('peninjau')->whereKey($this->pilih)->get()
            : $this->kueri()->with('peninjau')->get();
    }

    /** Kueri daftar (saringan + urutan) — dipakai tabel, ekspor, dan navigasi detail. */
    protected function kueri()
    {
        $urutan = match ($this->urut) {
            'nama' => ['nama', 'asc'],
            'lama', 'tunggu' => ['created_at', 'asc'],
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
            ->when($this->fSorot !== '', fn ($q) => $q->where('sorot', $this->fSorot === 'ya'))
            ->when($this->fBeranda !== '', fn ($q) => $this->fBeranda === 'ya'
                ? $q->tampilPublik()
                : $q->where(fn ($t) => $t->where('status', '!=', 'active')
                    ->orWhere(fn ($r) => $r->where('rating', '<', Testimoni::RATING_MIN_TAMPIL)->where('sorot', false))))
            ->when($this->fDari !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->fDari))
            ->when($this->fSampai !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->fSampai))
            ->when($this->searchTestimoni !== '', function ($q) {
                $term = "%{$this->searchTestimoni}%";
                // Nomor WhatsApp ikut dicari lewat bentuk INTI-nya: nomor yang
                // sama bisa tersimpan "0895…" atau "62895…".
                $inti = \App\Models\Customer::normalisasiNoHp($this->searchTestimoni);

                $q->where(function ($sub) use ($term, $inti) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('peran', 'like', $term)
                        ->orWhere('pesan', 'like', $term);

                    if ($inti !== '') {
                        $sub->orWhere('no_hp', 'like', '%'.$inti.'%');
                    }
                });
            })
            // "Paling lama menunggu": kiriman yang belum ditinjau naik dulu.
            ->when($this->urut === 'tunggu', fn ($q) => $q->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END"))
            // Yang disorot selalu di atas, sisanya menurut pilihan pengurutan.
            ->when($this->urut !== 'tunggu', fn ($q) => $q->orderByDesc('sorot'))
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

        // Dibaca SEKALI per render; dulu tiga kali (setelan = satu kueri tiap
        // dipanggil). Sengaja tidak di-cache statis: pekerja antrean berumur
        // panjang akan memegang nilai basi berjam-jam.
        $maksBeranda = Testimoni::jumlahBeranda();

        // Satu kueri untuk semua hitungan tab; dulu lima kueri terpisah.
        $perStatus = Testimoni::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $tabCounts = [
            'all' => (int) $perStatus->sum(),
            'pending' => (int) ($perStatus['pending'] ?? 0),
            'active' => (int) ($perStatus['active'] ?? 0),
            'non-active' => (int) ($perStatus['non-active'] ?? 0),
            'arsip' => Testimoni::onlyTrashed()->count(),
        ];

        // Sebaran bintang dari yang disetujui — sumber angka rata-rata.
        $sebaran = Testimoni::where('status', 'active')
            ->selectRaw('rating, count(*) as jumlah')
            ->groupBy('rating')->pluck('jumlah', 'rating');
        $totalSebaran = max(1, (int) $sebaran->sum());
        // Rata-rata & jumlah tampil dihitung dari sebaran yang sudah ada,
        // bukan dua kueri agregat tambahan.
        $jumlahDisetujui = (int) $sebaran->sum();
        $rataRating = $jumlahDisetujui
            ? round($sebaran->reduce(fn ($t, $n, $b) => $t + ($b * $n), 0) / $jumlahDisetujui, 1)
            : 0.0;

        return view('livewire.pages.admin.testimoni.testimoni-list', [
            'Testimoni' => $Testimoni,
            'tabCounts' => $tabCounts,
            // Rata-rata rating yang TAMPIL di publik (hanya yang disetujui).
            'rataRating' => $rataRating,
            'sebaran' => collect(range(5, 1))->mapWithKeys(fn ($b) => [$b => [
                'jumlah' => (int) ($sebaran[$b] ?? 0),
                'persen' => round(((int) ($sebaran[$b] ?? 0)) / $totalSebaran * 100),
            ]]),
            'jumlahTampil' => Testimoni::tampilPublik()->count(),
            // Hanya sebanyak yang MUAT di beranda: sisanya tidak pernah dilihat
            // pengunjung, jadi tak perlu ditarik hanya untuk mencari nomornya.
            'nomorTampil' => Testimoni::tampilPublik()->urutTampil()
                ->limit($maksBeranda)->pluck('id')->flip()->map(fn ($i) => $i + 1),
            // Dua kueri untuk satu halaman, bukan dua kueri per kartu.
            'konteksCuriga' => Testimoni::konteksKecurigaan($Testimoni->getCollection()),
            'detail' => $detail = $this->lihatId
                ? Testimoni::withTrashed()->with(['customer' => $pelangganHitung, 'peninjau'])->find($this->lihatId)
                : null,
            'riwayatDetail' => $detail ? RiwayatTestimoni::untuk($detail) : collect(),
            // Apa yang pernah dibeli pengirim — penilai kewajaran tercepat,
            // sebelumnya harus buka Data Pelanggan dulu.
            'produkDibeli' => $detail?->customer_id
                ? \App\Models\OrderItem::whereHas('order', fn ($o) => $o->where('customer_id', $detail->customer_id)->where('status', 'completed'))
                    ->latest('id')->limit(4)->pluck('product_name')->unique()->values()
                : collect(),
            'aktivitas' => $this->lihatAktivitas ? RiwayatTestimoni::terbaru() : collect(),
            'maksBeranda' => $maksBeranda,
            // Sembilan (atau sebanyak setelan) testimoni teratas, untuk pratinjau urutan beranda.
            'urutanBeranda' => $this->pratinjauBeranda
                ? Testimoni::tampilPublik()->urutTampil()->take($maksBeranda)->get()
                : collect(),
            'tolak' => $this->tolakId && $this->tolakId !== 'massal' ? Testimoni::find($this->tolakId) : null,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
