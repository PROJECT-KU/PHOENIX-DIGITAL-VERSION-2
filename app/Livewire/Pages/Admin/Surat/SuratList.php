<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratKlasifikasi;
use App\Support\Surat\SaringanSurat;
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
        /*
         | Terhapus ditaruh sebagai TAB, bukan layar tersembunyi.
         |
         | Surat yang dihapus tidak benar-benar hilang (hapus lembut), dan
         | selama ini satu-satunya cara mengembalikannya adalah lewat basis
         | data. Yang paling sering dihapus justru yang baru saja salah
         | dicatat — dan orang yang salah menekan tombol tidak akan menelepon
         | pemrogram, ia akan mencatat ulang suratnya dengan nomor agenda baru.
         */
        'terhapus' => ['Terhapus', 'bi-trash', '#64748b', 'Bisa dikembalikan'],
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

    /**
     * Kembalikan surat yang terhapus ke buku agenda.
     *
     * Nomor agendanya tidak pernah dilepas selama terhapus (lihat
     * Surat::nomorAgendaBaru yang ikut melihat baris terhapus), jadi
     * pemulihannya tidak pernah bertabrakan dengan surat yang dicatat
     * sesudahnya.
     */
    public function pulihkan(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('delete_surat'), 403);

        $surat = Surat::onlyTrashed()->terlihatOleh(auth()->user())->findOrFail($id);
        $surat->restore();

        $this->dispatch('swal-success', message: 'Surat '.$surat->nomor_agenda.' dikembalikan ke buku agenda.');
    }

    /**
     * Hapus selamanya — beserta baris pindaiannya.
     *
     * Disediakan karena tanpa itu daftar terhapus tumbuh selamanya dan
     * berhenti bisa dibaca. Berkas pindaian di cakram sengaja TIDAK ikut
     * dihapus di sini: menghapus berkas adalah satu-satunya langkah yang
     * tidak bisa dibatalkan sama sekali, dan di hosting bersama satu jalur
     * berkas yang salah bisa menyapu lebih dari yang dimaksud.
     */
    public function hapusPermanen(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('delete_surat'), 403);

        $surat = Surat::onlyTrashed()->terlihatOleh(auth()->user())->findOrFail($id);
        $nomor = $surat->nomor_agenda;
        $surat->forceDelete();

        $this->dispatch('swal-success', message: 'Surat '.$nomor.' dihapus selamanya.');
    }

    /**
     * Satu tempat untuk tab + saringan — dipakai daftar, penghitung tab,
     * DAN tombol ekspor.
     *
     * Logikanya tinggal di App\Support\Surat\SaringanSurat supaya berkas
     * ekspor memulangkan kumpulan surat yang sama persis dengan yang terlihat
     * di layar. Begitu disalin, salah satunya akan diam-diam melepaskan satu
     * saringan dan tidak ada yang menyadarinya.
     */
    private function dasar(?string $tab = null): Builder
    {
        return SaringanSurat::kueri(auth()->user(), $this->saringan($tab));
    }

    /** Saringan sekarang, dalam bentuk yang juga dipakai alamat ekspor. */
    public function saringan(?string $tab = null): array
    {
        return [
            'tab' => $tab ?? $this->tab,
            'cari' => $this->cari,
            'klasifikasi' => $this->klasifikasi,
            'sifat' => $this->sifat,
            'status' => $this->status,
            'dari' => $this->tglDari,
            'sampai' => $this->tglSampai,
            'urut' => $this->urut,
        ];
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
        $surat = SaringanSurat::urut($this->dasar(), $this->urut);

        $jumlahTab = [];

        foreach (array_keys(self::TAB) as $tab) {
            $jumlahTab[$tab] = $this->dasar($tab)->count();
        }

        return view('livewire.pages.admin.surat.daftar', [
            'surat' => $surat->with('klasifikasi')->paginate($this->perHalaman),
            'jumlahTab' => $jumlahTab,
            'daftarKlasifikasi' => SuratKlasifikasi::tersusun()->get(),
            'bolehCatat' => (bool) auth()->user()?->hasPermission('create_surat'),
            'bolehHapus' => (bool) auth()->user()?->hasPermission('delete_surat'),
        ]);
    }
}
