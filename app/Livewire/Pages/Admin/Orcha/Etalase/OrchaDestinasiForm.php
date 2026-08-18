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
        ])->layout('livewire.layout.templateindex');
    }
}
