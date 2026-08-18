<?php

namespace App\Livewire\Pages\Admin\Orcha\Etalase;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tambah dan ubah destinasi populer — halaman tersendiri, bukan jendela.
 *
 * Sebagai jendela, isiannya terus bertambah sampai harus digulung di dalam
 * kotak yang mengambang: nama, wilayah, provinsi, keterangan, perkiraan
 * pengunjung, foto utama, dan tiga gambar tambahan beserta pratinjaunya.
 * Jendela yang isinya menggulung sendiri menyembunyikan separuh isian dan
 * tombol simpannya, dan tidak menyisakan tempat untuk memperlihatkan hasilnya.
 */
class OrchaDestinasiForm extends Component
{
    use MemanggilOrcha, WithFileUploads;

    public ?int $destinasiId = null;

    public bool $ubah = false;

    public string $nama = '';

    public string $wilayah = 'jawa';

    public string $provinsi = '';

    public string $deskripsi = '';

    public $totalPengunjung = 0;

    public $gambar;

    public ?string $gambarLama = null;

    /** Gambar tambahan yang BARU dipilih admin. */
    public array $subFoto = [];

    /**
     * Gambar tambahan yang sudah tersimpan dan MASIH dipertahankan.
     *
     * Daftar inilah yang menentukan isi akhir, bukan isi lama di Orcha:
     * menghapus satu gambar berarti mengeluarkannya dari sini. Berkasnya baru
     * dibuang setelah admin menyimpan, jadi meninggalkan halaman tanpa
     * menyimpan tidak menghilangkan apa pun.
     *
     * @var list<string>
     */
    public array $subFotoTetap = [];

    /** Batas dari Orcha; kartu publiknya hanya menampung sekian gambar. */
    public int $batasSubFoto = 3;

    /**
     * Keterangan asal usulan lokasi, untuk ditampilkan di bawah isian nama.
     *
     * Usulan yang mengisi dua isian tanpa mengatakan apa-apa terasa seperti
     * sistem yang mengubah pekerjaan admin diam-diam. Menyebut asalnya membuat
     * admin tahu itu tebakan yang boleh dibetulkan.
     */
    public string $usulanLokasi = '';

    public function mount(?int $destinasi = null): void
    {
        if (! $destinasi) {
            return;
        }

        $this->destinasiId = $destinasi;
        $this->ubah = true;

        $isi = $this->muat("/destinasi/{$destinasi}")['data'] ?? [];

        if ($isi === []) {
            return;
        }

        $this->nama = (string) ($isi['nama'] ?? '');
        $this->wilayah = (string) ($isi['wilayah'] ?? 'jawa');
        $this->provinsi = (string) ($isi['provinsi'] ?? '');
        $this->deskripsi = (string) ($isi['deskripsi'] ?? '');
        $this->totalPengunjung = $isi['total_pengunjung'] ?? 0;
        $this->gambarLama = $isi['foto'] ?? null;
        $this->subFotoTetap = array_values($isi['sub_foto'] ?? []);
        $this->batasSubFoto = (int) ($isi['batas_sub_foto'] ?? 3);
    }

    /**
     * Provinsi menentukan wilayahnya sendiri.
     *
     * Sebelumnya keduanya diketik/dipilih terpisah, dan "Jawa Timur" yang
     * tercatat di wilayah "Bali & Nusa Tenggara" tidak akan pernah ketahuan
     * sampai ada pengunjung yang menyaring dan tidak menemukannya.
     */
    public function updatedProvinsi(): void
    {
        $wilayah = $this->petaProvinsi()[$this->provinsi] ?? null;

        if ($wilayah) {
            $this->wilayah = $wilayah;
        }
    }

    /**
     * Wilayah menyaring pilihan provinsinya — merek menyaring nama unit, sama
     * seperti di formulir armada.
     *
     * Provinsi yang tidak termasuk wilayah baru DIKOSONGKAN. Membiarkannya
     * berarti kartu destinasi menyatakan dua hal yang bertentangan — "Jawa
     * Timur" di wilayah "Bali & Nusa Tenggara" — dan yang salah baru ketahuan
     * saat ada pengunjung menyaring dan tidak menemukannya.
     */
    public function updatedWilayah(): void
    {
        if ($this->provinsi === '') {
            return;
        }

        if (($this->petaProvinsi()[$this->provinsi] ?? null) !== $this->wilayah) {
            $this->provinsi = '';
        }
    }

    /**
     * Provinsi yang termasuk wilayah terpilih.
     *
     * Menampilkan seluruh 38 provinsi setelah wilayahnya dipilih membuat admin
     * menyaring sendiri di kepala, dan membuka pintu untuk memilih provinsi
     * yang tidak mungkin ada di wilayah itu.
     *
     * @return list<string>
     */
    public function provinsiTersedia(): array
    {
        $peta = $this->petaProvinsi();

        $cocok = array_keys(array_filter(
            $peta,
            fn (string $wilayah) => $wilayah === $this->wilayah,
        ));

        // Wilayah yang belum punya daftar provinsi tidak boleh mengunci isian:
        // yang tampil kembali seluruhnya, bukan kosong.
        return $cocok ?: array_keys($peta);
    }

    /**
     * @return array<string, string> nama provinsi => kunci wilayah
     */
    public function petaProvinsi(): array
    {
        return $this->rujukan('provinsi_wilayah');
    }

    /**
     * Provinsi yang ditambahkan admin sendiri — hanya ini yang boleh dihapus
     * dari daftar pilihan; yang bawaan ikut versi kode.
     */
    public function provinsiKustom(): array
    {
        return $this->rujukan('provinsi_kustom');
    }

    /**
     * Menambahkan provinsi yang belum terdaftar, langsung ke daftar pilihan.
     *
     * Wilayahnya diambil dari yang sedang dipilih di formulir: provinsi tanpa
     * wilayah tidak masuk penyaring mana pun di halaman publik, dan
     * destinasinya menghilang dari daftar.
     */
    public function tambahProvinsi(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '') {
            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/provinsi', [
                'nama' => $nama,
                'wilayah' => $this->wilayah,
            ]);

            $this->provinsi = $nama;
            $this->segarkanProvinsi($balasan['pesan'] ?? 'Provinsi ditambahkan.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Menambahkan wilayah yang belum terdaftar, langsung ke daftar pilihan.
     *
     * Wilayah bukan sekadar isian: ia jadi tab penyaring di halaman publik.
     * Karena itu Orcha yang menentukan kuncinya dari labelnya — kunci yang
     * dibuat dua tempat berbeda cepat atau lambat akan berbeda bentuknya.
     */
    public function tambahWilayah(string $label): void
    {
        $label = trim($label);

        if ($label === '') {
            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/wilayah', ['label' => $label]);

            cache()->forget('orcha.rujukan');

            // Wilayah yang baru dibuat langsung dipilih; kuncinya dibaca dari
            // daftar terbaru, bukan ditebak sendiri di sini.
            $kunci = array_search($label, $this->rujukan('wilayah'), true);

            if ($kunci !== false) {
                $this->wilayah = $kunci;
                $this->updatedWilayah();
            }

            $this->kabarkanWilayah($balasan['pesan'] ?? 'Wilayah ditambahkan.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Menghapus wilayah tambahan. Orcha menolak bila masih dipakai destinasi —
     * pesannya diteruskan apa adanya supaya admin tahu harus berbuat apa.
     */
    public function hapusWilayah(int $id): void
    {
        try {
            $balasan = $this->orcha()->hapus("/wilayah/{$id}");

            cache()->forget('orcha.rujukan');
            $this->kabarkanWilayah($balasan['pesan'] ?? 'Wilayah dihapus.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    private function kabarkanWilayah(string $pesan): void
    {
        $this->dispatch('orcha-wilayah-segar',
            daftar: $this->rujukan('wilayah'),
            kustom: $this->rujukan('wilayah_kustom'),
        );

        $this->dispatch('order-updated', message: $pesan);
    }

    public function hapusProvinsi(int $id): void
    {
        try {
            $balasan = $this->orcha()->hapus("/provinsi/{$id}");

            $this->segarkanProvinsi($balasan['pesan'] ?? 'Provinsi dihapus.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Membuang simpanan rujukan lalu mengirim daftar terbaru ke layar.
     *
     * Tanpa membuang simpanannya, daftar yang baru saja diubah baru terlihat
     * sepuluh menit kemudian — dan admin mengira penambahannya gagal.
     */
    private function segarkanProvinsi(string $pesan): void
    {
        cache()->forget('orcha.rujukan');

        $this->dispatch('orcha-provinsi-segar',
            peta: $this->petaProvinsi(),
            kustom: $this->provinsiKustom(),
        );

        $this->dispatch('order-updated', message: $pesan);
    }

    /**
     * Katalog nama destinasi beserta provinsinya.
     *
     * @return array<string, string|null>
     */
    public function katalogDestinasi(): array
    {
        return $this->rujukan('katalog_destinasi');
    }

    public function katalogDestinasiKustom(): array
    {
        return $this->rujukan('katalog_destinasi_kustom');
    }

    /**
     * Memilih nama dari katalog sekaligus mengisi provinsi dan wilayahnya.
     *
     * Satu tindakan mengisi tiga isian. Provinsi yang sudah ditulis admin tidak
     * ditimpa — nama tempat yang mirip ada di beberapa provinsi, dan tebakan
     * tidak berhak mengalahkan keputusan.
     */
    public function pilihDestinasi(string $nama): void
    {
        $this->nama = $nama;
        $this->usulanLokasi = '';

        $provinsi = $this->katalogDestinasi()[$nama] ?? null;

        if (! $provinsi || $this->provinsi !== '') {
            return;
        }

        $wilayah = $this->petaProvinsi()[$provinsi] ?? null;

        if (! $wilayah) {
            return;
        }

        $this->provinsi = $provinsi;
        $this->wilayah = $wilayah;
        $this->usulanLokasi = 'Provinsi dan wilayah terisi dari daftar destinasi — betulkan bila keliru.';
    }

    /**
     * Menambahkan nama yang belum terdaftar, lalu memilihnya.
     *
     * Provinsinya dicari Orcha sendiri bila belum diketahui, jadi sekali tulis
     * pun tiga isian bisa langsung terisi.
     */
    public function tambahDestinasi(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '') {
            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/katalog-destinasi', ['nama' => $nama]);

            cache()->forget('orcha.rujukan');
            $this->pilihDestinasi($nama);
            $this->kabarkanKatalog($balasan['pesan'] ?? 'Destinasi ditambahkan ke daftar.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function hapusKatalogDestinasi(int $id): void
    {
        try {
            $balasan = $this->orcha()->hapus("/katalog-destinasi/{$id}");

            cache()->forget('orcha.rujukan');
            $this->kabarkanKatalog($balasan['pesan'] ?? 'Dihapus dari daftar.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    private function kabarkanKatalog(string $pesan): void
    {
        $this->dispatch('orcha-katalog-destinasi-segar',
            katalog: $this->katalogDestinasi(),
            kustom: $this->katalogDestinasiKustom(),
        );

        $this->dispatch('order-updated', message: $pesan);
    }

    /**
     * Nama destinasi mengusulkan provinsi dan wilayahnya.
     *
     * Hanya MENGISI YANG MASIH KOSONG. Menimpa provinsi yang sudah ditulis admin
     * berarti tebakan mengalahkan keputusan — dan tebakan tentang nama tempat
     * yang mirip ("Pantai Baru" ada di beberapa provinsi) cukup sering meleset.
     */
    public function updatedNama(): void
    {
        $this->usulanLokasi = '';

        if ($this->provinsi !== '' || mb_strlen(trim($this->nama)) < 4) {
            return;
        }

        $usulan = $this->cariLokasi($this->nama);

        if ($usulan === null) {
            return;
        }

        $this->provinsi = $usulan['provinsi'];
        $this->wilayah = $usulan['wilayah'];

        $this->usulanLokasi = ($usulan['sumber'] ?? '') === 'destinasi'
            ? 'Terisi dari destinasi lain yang namanya mirip — betulkan bila keliru.'
            : 'Terisi dari peta OpenStreetMap — betulkan bila keliru.';
    }

    /**
     * Bertanya ke Orcha, dan diam bila tidak terjawab.
     *
     * Usulan yang gagal bukan kegagalan admin: yang benar adalah ia mengisi
     * sendiri seperti biasa, bukan melihat pesan galat yang tidak bisa
     * ditindaklanjuti.
     */
    private function cariLokasi(string $nama): ?array
    {
        try {
            return $this->orcha()->ambil('/cari-lokasi', ['nama' => $nama])['data'] ?? null;
        } catch (\App\Exceptions\OrchaTidakTerjangkau) {
            return null;
        }
    }

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:191',
            'wilayah' => 'required|string',
            'provinsi' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string|max:1000',
            'totalPengunjung' => 'nullable|integer|min:0',
            'gambar' => 'nullable|image|max:4096',
            'subFoto.*' => 'image|max:2048',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama destinasi',
            'totalPengunjung' => 'perkiraan pengunjung',
            'gambar' => 'foto utama',
            'subFoto.*' => 'gambar tambahan',
        ];
    }

    /**
     * Mengeluarkan satu gambar tambahan dari daftar yang dipertahankan.
     *
     * Belum menghapus berkasnya: penghapusan baru terjadi di Orcha saat
     * perubahan disimpan.
     */
    public function hapusSubFoto(string $jalur): void
    {
        $this->subFotoTetap = array_values(array_filter(
            $this->subFotoTetap,
            fn (string $tersimpan) => $tersimpan !== $jalur,
        ));
    }

    /** Membatalkan satu berkas yang baru dipilih, sebelum terkirim. */
    public function batalkanSubFoto(int $urutan): void
    {
        unset($this->subFoto[$urutan]);
        $this->subFoto = array_values($this->subFoto);
    }

    /**
     * Sisa tempat gambar tambahan.
     *
     * Yang baru dipilih ikut dihitung: keduanya sama-sama akan tersimpan, dan
     * menghitung yang tersimpan saja menjanjikan tempat yang sudah terpakai.
     */
    public function sisaSubFoto(): int
    {
        return max(0, $this->batasSubFoto - count($this->subFotoTetap) - count($this->subFoto));
    }

    public function simpan(): void
    {
        $this->validate();

        if (count($this->subFotoTetap) + count($this->subFoto) > $this->batasSubFoto) {
            $this->addError('subFoto', 'Gambar tambahan maksimal '.$this->batasSubFoto
                .'. Hapus dulu salah satu sebelum menambah.');

            return;
        }

        $data = [
            'nama' => $this->nama,
            'wilayah' => $this->wilayah,
            'provinsi' => $this->provinsi,
            'deskripsi' => $this->deskripsi,
            'total_pengunjung' => $this->totalPengunjung ?: 0,
            // Selalu dikirim, termasuk saat kosong: daftar kosong berarti "semua
            // gambar tambahan dihapus", dan itu keputusan yang harus bisa
            // dinyatakan.
            'sub_foto_tetap' => $this->subFotoTetap,
        ];

        $jalur = '/destinasi'.($this->destinasiId ? '/'.$this->destinasiId : '');
        $pesan = $this->ubah ? 'Destinasi diperbarui di Orcha.' : 'Destinasi ditambahkan di Orcha.';

        $this->kirimData(
            $jalur,
            $data,
            $pesan,
            $this->gambar,
            route('admin.orcha.destinasi'),
            $this->subFoto ? ['sub_foto' => $this->subFoto] : [],
        );
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.etalase.destinasi-form', [
            'daftarWilayah' => $this->rujukan('wilayah'),
            'wilayahKustom' => $this->rujukan('wilayah_kustom'),
            'katalogDestinasi' => $this->katalogDestinasi(),
            'katalogDestinasiKustom' => $this->katalogDestinasiKustom(),
            'daftarProvinsi' => $this->provinsiTersedia(),
            'petaProvinsi' => $this->petaProvinsi(),
            'provinsiKustom' => $this->provinsiKustom(),
        ])->layout('livewire.layout.templateindex');
    }
}
