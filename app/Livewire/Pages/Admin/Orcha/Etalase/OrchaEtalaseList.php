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
            ],
        };
    }

    /**
     * Destinasi tidak lagi diurus lewat jendela.
     *
     * Pengalihannya dipasang DI SINI, bukan hanya pada tombolnya: pemanggilan
     * yang tertinggal di tempat lain akan membuka jendela setengah jadi yang
     * tidak mengenal gambar tambahan, dan admin baru sadar setelah menyimpan.
     */
    public function tambah(): void
    {
        if ($this->jenis === 'destinasi') {
            $this->redirectRoute('admin.orcha.destinasi.tambah', navigate: true);

            return;
        }

        $this->kosongkan();
        $this->formTerbuka = true;
    }

    public function ubah(array $baris): void
    {
        if ($this->jenis === 'destinasi') {
            $this->redirectRoute('admin.orcha.destinasi.ubah', ['destinasi' => $baris['id']], navigate: true);

            return;
        }

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
            ],
        };

        $jalur = '/'.$this->jenis.($this->sedangDiubah ? '/'.$this->sedangDiubah : '');
        $pesan = $this->sedangDiubah ? 'Data diperbarui di Orcha.' : 'Data ditambahkan di Orcha.';

        if ($this->kirimData($jalur, $data, $pesan, $this->gambar)) {
            $this->tutup();
        }
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
        $this->reset(['sedangDiubah', 'nama', 'provinsi', 'deskripsi', 'isi', 'gambar', 'gambarLama']);
        $this->wilayah = 'jawa';
        $this->totalPengunjung = 0;
        $this->rating = 5;
        $this->resetValidation();
    }

    /**
     * Sembilan, bukan sepuluh.
     *
     * Daftarnya berbentuk kartu tiga kolom (col-xl-4), jadi sembilan mengisi
     * tepat tiga baris penuh. Sepuluh menyisakan satu kartu sendirian di baris
     * keempat — baris yang tampak seperti kesalahan tata letak, bukan akhir
     * daftar. Angka yang sama dipakai daftar armada.
     */
    protected function perHalaman(): int
    {
        return 9;
    }

    public function render()
    {
        // Hanya destinasi yang dipenggal per halaman. Testimoni dan partner
        // masih dikirim Orcha sekaligus; menyodorkan nomor halaman untuk daftar
        // yang tidak berhalaman hanya menjanjikan yang tidak ada.
        $berhalaman = $this->jenis === 'destinasi';

        $parameter = [];

        if ($berhalaman) {
            $parameter = $this->parameterDaftar();
            unset($parameter['status']);

            if ($this->filterStatus !== '') {
                $parameter['wilayah'] = $this->filterStatus;
            }
        }

        $hasil = $this->muat('/'.$this->jenis, $parameter);

        $daftar = collect($hasil['data'] ?? []);

        // Testimoni dan partner dikirim seluruhnya (jumlahnya sedikit), jadi
        // pencariannya cukup di sini. Untuk destinasi TIDAK: penyaring di sini
        // hanya melihat sembilan baris yang kebetulan sedang tampil, sehingga
        // yang dicari admin akan "tidak ditemukan" padahal ada di halaman lain.
        if (! $berhalaman && $this->cari !== '') {
            $kata = mb_strtolower($this->cari);
            $daftar = $daftar->filter(fn ($baris) => str_contains(mb_strtolower(
                ($baris['nama'] ?? '').' '.($baris['provinsi'] ?? '').' '.($baris['isi'] ?? '')
            ), $kata))->values();
        }

        return view('livewire.pages.admin.orcha.etalase.index', [
            'daftar' => $daftar->all(),
            // Orcha dipasang terpisah dan boleh tertinggal sekian rilis: yang
            // belum mengirim meta menghasilkan daftar utuh tanpa nomor halaman,
            // bukan halaman yang galat.
            'meta' => $hasil['meta'] ?? [],
            'pilihanWilayah' => $this->rujukan('wilayah'),
        ])->layout('livewire.layout.templateindex');
    }
}
