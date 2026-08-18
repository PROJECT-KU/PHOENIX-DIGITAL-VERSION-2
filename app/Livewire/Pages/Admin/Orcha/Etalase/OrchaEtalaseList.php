<?php

namespace App\Livewire\Pages\Admin\Orcha\Etalase;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Destinasi populer, testimoni, dan partner Orcha.
 *
 * Ketiganya isiannya sedikit, jadi tambah/ubahnya cukup lewat jendela di
 * halaman yang sama — tidak perlu halaman formulir tersendiri.
 */
class OrchaEtalaseList extends Component
{
    use MemanggilOrcha, WithFileUploads;

    /** 'destinasi', 'testimoni', atau 'partner' — ditentukan rute. */
    public string $jenis = 'destinasi';

    public bool $formTerbuka = false;

    public ?int $sedangDiubah = null;

    // Isian gabungan; tiap jenis memakai yang relevan saja.
    public string $nama = '';

    public string $wilayah = 'jawa';

    public string $provinsi = '';

    public string $deskripsi = '';

    public $totalPengunjung = 0;

    public int $rating = 5;

    public string $isi = '';

    public $gambar;

    public ?string $gambarLama = null;

    /**
     * Gambar tambahan destinasi — yang BARU dipilih admin.
     *
     * Kartu destinasi di halaman publik menampilkan gambar tambahan di bawah
     * keterangannya, tetapi jendela ini dulu hanya mengenal satu foto. Admin
     * yang mengurus destinasi dari sini tidak punya cara menambah maupun
     * menghapusnya — harus membuka admin bawaan Orcha, yang justru dihindari
     * dengan adanya halaman ini.
     */
    public array $subFoto = [];

    /**
     * Gambar tambahan yang sudah tersimpan dan MASIH dipertahankan.
     *
     * Daftar inilah yang menentukan isi akhir, bukan isi lama di Orcha:
     * menghapus satu gambar berarti mengeluarkannya dari sini. Berkasnya baru
     * benar-benar dibuang setelah admin menekan Simpan, jadi menutup jendela
     * tanpa menyimpan tidak menghilangkan apa pun.
     *
     * @var list<string>
     */
    public array $subFotoTetap = [];

    /** Batas dari Orcha; kartu publiknya hanya menampung sekian gambar. */
    public int $batasSubFoto = 3;

    public function mount(string $jenis = 'destinasi'): void
    {
        $this->jenis = in_array($jenis, ['destinasi', 'testimoni', 'partner'], true) ? $jenis : 'destinasi';
    }

    protected function rules(): array
    {
        return match ($this->jenis) {
            'testimoni' => [
                'nama' => 'required|string|max:191',
                'rating' => 'required|integer|min:1|max:5',
                'isi' => 'required|string|max:1000',
                'gambar' => 'nullable|image|max:4096',
            ],
            'partner' => [
                'nama' => 'required|string|max:191',
                'gambar' => 'nullable|image|max:4096',
            ],
            default => [
                'nama' => 'required|string|max:191',
                'wilayah' => 'required|string',
                'provinsi' => 'nullable|string|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'totalPengunjung' => 'nullable|integer|min:0',
                'gambar' => 'nullable|image|max:4096',
                'subFoto.*' => 'image|max:2048',
            ],
        };
    }

    public function tambah(): void
    {
        $this->kosongkan();
        $this->formTerbuka = true;
    }

    public function ubah(array $baris): void
    {
        $this->kosongkan();

        $this->sedangDiubah = (int) $baris['id'];
        $this->nama = (string) ($baris['nama'] ?? '');
        $this->wilayah = (string) ($baris['wilayah'] ?? 'jawa');
        $this->provinsi = (string) ($baris['provinsi'] ?? '');
        $this->deskripsi = (string) ($baris['deskripsi'] ?? '');
        $this->totalPengunjung = $baris['total_pengunjung'] ?? 0;
        $this->rating = (int) ($baris['rating'] ?? 5);
        $this->isi = (string) ($baris['isi'] ?? '');
        $this->gambarLama = $baris['foto'] ?? $baris['logo'] ?? null;
        $this->subFotoTetap = array_values($baris['sub_foto'] ?? []);
        $this->batasSubFoto = (int) ($baris['batas_sub_foto'] ?? 3);

        $this->formTerbuka = true;
    }

    public function simpan(): void
    {
        $this->validate();

        $data = match ($this->jenis) {
            'testimoni' => ['nama' => $this->nama, 'rating' => $this->rating, 'isi' => $this->isi],
            'partner' => ['nama' => $this->nama],
            default => [
                'nama' => $this->nama,
                'wilayah' => $this->wilayah,
                'provinsi' => $this->provinsi,
                'deskripsi' => $this->deskripsi,
                'total_pengunjung' => $this->totalPengunjung ?: 0,
                // Selalu dikirim, termasuk saat kosong: daftar kosong berarti
                // "semua gambar tambahan dihapus", dan itu keputusan yang harus
                // bisa dinyatakan. Orcha membedakannya dari medan yang memang
                // tidak disebut sama sekali.
                'sub_foto_tetap' => $this->subFotoTetap,
            ],
        };

        $jalur = '/'.$this->jenis.($this->sedangDiubah ? '/'.$this->sedangDiubah : '');
        $pesan = $this->sedangDiubah ? 'Data diperbarui di Orcha.' : 'Data ditambahkan di Orcha.';

        if ($this->jenis === 'destinasi'
            && count($this->subFotoTetap) + count($this->subFoto) > $this->batasSubFoto) {
            $this->addError('subFoto', 'Gambar tambahan maksimal '.$this->batasSubFoto
                .'. Hapus dulu salah satu sebelum menambah.');

            return;
        }

        $berkasLain = $this->subFoto ? ['sub_foto' => $this->subFoto] : [];

        if ($this->kirimData($jalur, $data, $pesan, $this->gambar, null, $berkasLain)) {
            $this->tutup();
        }
    }

    /**
     * Mengeluarkan satu gambar tambahan dari daftar yang dipertahankan.
     *
     * Belum menghapus berkasnya: penghapusan baru terjadi di Orcha saat
     * perubahan disimpan. Dengan begitu admin yang berubah pikiran cukup
     * menutup jendela tanpa kehilangan apa pun.
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
     * Yang baru dipilih ikut dihitung, karena keduanya sama-sama akan tersimpan.
     * Menghitung yang tersimpan saja membuat tulisannya menjanjikan tempat yang
     * sebenarnya sudah terpakai.
     */
    public function sisaSubFoto(): int
    {
        return max(0, $this->batasSubFoto - count($this->subFotoTetap) - count($this->subFoto));
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/{$this->jenis}/{$id}", 'Data dihapus di Orcha.');
    }

    public function tutup(): void
    {
        $this->formTerbuka = false;
        $this->kosongkan();
    }

    private function kosongkan(): void
    {
        $this->reset(['sedangDiubah', 'nama', 'provinsi', 'deskripsi', 'isi', 'gambar', 'gambarLama',
            'subFoto', 'subFotoTetap']);
        $this->wilayah = 'jawa';
        $this->totalPengunjung = 0;
        $this->rating = 5;
        $this->resetValidation();
    }

    public function render()
    {
        $hasil = $this->muat('/'.$this->jenis, $this->jenis === 'destinasi' && $this->filterStatus !== ''
            ? ['wilayah' => $this->filterStatus]
            : []);

        $daftar = collect($hasil['data'] ?? []);

        // Ketiga jalur ini mengirim seluruh data sekaligus (jumlahnya sedikit),
        // jadi pencariannya cukup dilakukan di sini.
        if ($this->cari !== '') {
            $kata = mb_strtolower($this->cari);
            $daftar = $daftar->filter(fn ($baris) => str_contains(mb_strtolower(
                ($baris['nama'] ?? '').' '.($baris['provinsi'] ?? '').' '.($baris['isi'] ?? '')
            ), $kata))->values();
        }

        return view('livewire.pages.admin.orcha.etalase.index', [
            'daftar' => $daftar->all(),
            'pilihanWilayah' => $this->rujukan('wilayah'),
        ])->layout('livewire.layout.templateindex');
    }
}
