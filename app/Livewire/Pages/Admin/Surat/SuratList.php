<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratKlasifikasi;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buku agenda surat: daftar surat masuk & keluar.
 *
 * Bentuknya mengikuti Pesanan Toko — kartu tab berisi angka, satu bilah
 * saringan, lalu tabel — karena itulah layar yang paling sering dipakai admin
 * di lemon, dan hafalan letaknya terbawa ke sini tanpa perlu belajar lagi.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Arsip Surat')]
class SuratList extends Component
{
    use WithPagination;

    /*
     | Tab DAN saringan ikut ke alamat halaman.
     |
     | Admin membuka detail surat lalu menekan Kembali berkali-kali dalam satu
     | jam kerja. Tanpa ini, kata pencarian dan periode harus diketik ulang
     | setiap kali — dan yang terjadi berikutnya bukan mengetik ulang,
     | melainkan berhenti menyaring sama sekali lalu menggulir seluruh arsip.
     */
    #[Url(as: 'tab', except: 'semua')]
    public string $tab = 'semua';

    #[Url(as: 'cari', except: '')]
    public string $cari = '';

    #[Url(as: 'klasifikasi', except: '')]
    public string $klasifikasi = '';

    #[Url(as: 'sifat', except: '')]
    public string $sifat = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'dari', except: '')]
    public string $tglDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $tglSampai = '';

    #[Url(as: 'urut', except: 'terbaru')]
    public string $urut = 'terbaru';

    #[Url(as: 'baris', except: 25)]
    public int $perHalaman = 25;

    /**
     * Tab: [label, ikon, warna, keterangan].
     *
     * Dua arah surat ditaruh sebagai tab, bukan sebagai kotak pilih, karena
     * itu pembagian paling dasar buku agenda — di kertas pun bukunya memang
     * dua. Tiga tab terakhir adalah tumpukan pekerjaan, bukan golongan surat.
     */
    public const TAB = [
        'semua' => ['Semua', 'bi-archive-fill', '#4f46e5', 'Seluruh arsip'],
        'masuk' => ['Surat Masuk', 'bi-envelope-arrow-down', '#0ea5c6', 'Yang kita terima'],
        'keluar' => ['Surat Keluar', 'bi-envelope-arrow-up', '#17a974', 'Yang kita kirim'],
        'baru' => ['Belum Ditindak', 'bi-envelope-exclamation', '#d97706', 'Sudah dicatat, belum dibaca'],
        'telat' => ['Lewat Tenggat', 'bi-hourglass-bottom', '#b91c1c', 'Disposisinya belum ditutup'],
        'retensi' => ['Masa Simpan Habis', 'bi-archive', '#7c3aed', 'Perlu diputuskan'],
    ];

    public const URUTAN = [
        'terbaru' => 'Tanggal surat terbaru di atas',
        'terlama' => 'Tanggal surat terlama di atas',
        'agenda' => 'Nomor agenda terbesar di atas',
        'dicatat' => 'Terakhir dicatat di atas',
    ];

    public const PILIHAN_BARIS = [10, 25, 50, 100];

    /** Saringan apa pun berubah → kembali ke halaman satu. */
    public function updated(string $nama): void
    {
        if (in_array($nama, ['tab', 'cari', 'klasifikasi', 'sifat', 'status', 'tglDari', 'tglSampai', 'urut', 'perHalaman'], true)) {
            $this->resetPage();
        }
    }

    public function pilihTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TAB) ? $tab : 'semua';
        $this->resetPage();
    }

    public function kosongkanSaringan(): void
    {
        $this->reset(['cari', 'klasifikasi', 'sifat', 'status', 'tglDari', 'tglSampai', 'urut']);
        $this->resetPage();
    }

    /**
     * Apakah ada saringan terpasang?
     *
     * TAB tidak dihitung sebagai saringan: ia penanda tempat, bukan
     * penyempitan. Tombol "Kosongkan saringan" yang juga mengembalikan tab
     * akan melempar admin ke daftar yang berbeda dari yang ia kira.
     */
    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->klasifikasi !== '' || $this->sifat !== ''
            || $this->status !== '' || $this->tglDari !== '' || $this->tglSampai !== ''
            || $this->urut !== 'terbaru';
    }

    /** Satu tempat untuk tab + saringan, dipakai daftar maupun penghitung tab. */
    private function dasar(?string $tab = null): Builder
    {
        $tab ??= $this->tab;

        $q = Surat::query()
            ->terlihatOleh(auth()->user())
            ->cari($this->cari)
            ->klasifikasi($this->klasifikasi ?: null)
            ->sifat($this->sifat ?: null)
            ->status($this->status ?: null)
            ->periode($this->tglDari ?: null, $this->tglSampai ?: null);

        return match ($tab) {
            'masuk' => $q->where('arah', 'masuk'),
            'keluar' => $q->where('arah', 'keluar'),
            'baru' => $q->where('status', 'baru'),
            'telat' => $q->whereHas('disposisi', fn (Builder $d) => $d->belumSelesai()
                ->whereNotNull('batas')
                ->whereDate('batas', '<', now()->toDateString())),
            'retensi' => $q->whereNotNull('musnah_pada')
                ->whereDate('musnah_pada', '<=', now()->toDateString())
                ->where('status', '!=', 'diarsipkan'),
            default => $q,
        };
    }

    public function render()
    {
        /*
         | Urutan SELALU punya pemecah seri (id).
         |
         | tanggal_surat hanya tanggal, tanpa jam: puluhan surat bisa bertanggal
         | sama, dan MySQL tidak menjamin urutan baris yang nilainya seri.
         | Tanpa pemecah, satu surat bisa terbit di dua halaman sekaligus
         | sementara surat lain tidak terbit di halaman mana pun.
         */
        $surat = match ($this->urut) {
            'terlama' => $this->dasar()->orderBy('tanggal_surat')->orderBy('id'),
            'agenda' => $this->dasar()->orderByDesc('nomor_agenda')->orderByDesc('id'),
            'dicatat' => $this->dasar()->orderByDesc('created_at')->orderByDesc('id'),
            default => $this->dasar()->orderByDesc('tanggal_surat')->orderByDesc('id'),
        };

        $jumlahTab = [];

        foreach (array_keys(self::TAB) as $tab) {
            $jumlahTab[$tab] = $this->dasar($tab)->count();
        }

        return view('livewire.pages.admin.surat.daftar', [
            'surat' => $surat->with('klasifikasi')->paginate($this->perHalaman),
            'jumlahTab' => $jumlahTab,
            'daftarKlasifikasi' => SuratKlasifikasi::tersusun()->get(),
            'bolehCatat' => (bool) auth()->user()?->hasPermission('create_surat'),
        ]);
    }
}
