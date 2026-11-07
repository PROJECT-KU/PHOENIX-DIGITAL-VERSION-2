<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Rab\Concerns\MembacaKatalogOrcha;
use App\Livewire\Pages\Admin\Orcha\Rab\Concerns\MenyuntingMasterHarga;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman tersendiri untuk membuat dan menyunting satu baris master harga.
 *
 * Formulirnya dulu terbuka DI DALAM daftar — kartu setinggi layar yang
 * menyelip di antara saringan dan tabel, mendorong daftarnya keluar
 * pandangan. Dengan belasan isian, dua panel tambahan (destinasi baru, vendor
 * baru), dan panel tempel-dari-spreadsheet, kartunya lebih panjang daripada
 * daftar yang ia tutupi.
 *
 * Yang paling mahal bukan panjangnya: alamatnya tidak pernah menunjuk baris
 * yang sedang disunting. Tidak bisa dibagikan ke rekan, tidak bisa dibuka di
 * tab kedua untuk dibandingkan, dan menekan Kembali meninggalkan seluruh
 * isian yang sudah diketik.
 */
class OrchaMasterHargaForm extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;
    use MembacaKatalogOrcha;
    use MenyuntingMasterHarga;

    public ?int $hargaId = null;

    public bool $ubah = false;

    /** Nama yang sudah tersimpan — dipakai judul halaman. */
    public string $namaTersimpan = '';

    /**
     * Saringan daftar yang sedang aktif, dibawa supaya Batal dan Simpan
     * mengembalikan admin ke daftar yang SAMA — bukan ke halaman satu tanpa
     * saringan, jauh dari baris yang baru saja ia kerjakan.
     */
    #[Url(as: 'kembali')]
    public string $kembali = '';

    /**
     * Membuat baris BARU dari salinan baris lain.
     *
     * Hiace dari dua vendor berbeda hanya berbeda di satu kotak; mengetik
     * ulang delapan kotak lainnya adalah delapan kesempatan salah ketik.
     */
    #[Url(as: 'salin')]
    public ?int $salin = null;

    public function mount(?int $harga = null): void
    {
        $this->kosongkan();

        if (! $harga) {
            $this->tambah = true;
            $this->muatSalinan();

            return;
        }

        $this->hargaId = $harga;
        $this->ubah = true;

        try {
            $baris = $this->orcha()->ambil("/master-harga/{$harga}")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        if (! isset($baris['nama'])) {
            return;
        }

        $this->namaTersimpan = (string) $baris['nama'];
        $this->bukaSunting((int) $baris['id'], $baris);
    }

    /**
     * Mengisi borang dari baris lain, sebagai baris baru.
     *
     * Dibaca ulang dari Orcha, bukan dititipkan lewat alamat: sembilan kolom
     * di query string bisa disunting siapa pun yang menempelkan tautannya.
     * Namanya diberi "(salinan)" supaya penjaga kembaran di Orcha tidak
     * menolaknya, sekaligus supaya yang lupa menggantinya tetap terbaca.
     */
    private function muatSalinan(): void
    {
        if (! $this->salin) {
            return;
        }

        try {
            $baris = $this->orcha()->ambil("/master-harga/{$this->salin}")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        if (! isset($baris['nama'])) {
            return;
        }

        $this->bukaSunting((int) $baris['id'], $baris);

        $this->sunting = null;
        $this->tambah = true;
        $this->isian['nama'] = trim($baris['nama'].' (salinan)');
    }

    public function tujuanKembali(): string
    {
        $dasar = route('admin.orcha.master-harga');

        return $this->kembali !== '' ? $dasar.'?'.$this->kembali : $dasar;
    }

    public function render()
    {
        $katalog = $this->katalog();

        /*
         | Destinasi yang ditawarkan mengikuti provinsi terpilih — daftar 70
         | destinasi seluruh Indonesia untuk tiket Jogja hanya menambah
         | gulungan. Yang dipakai menandai "belum terdaftar" tetap seluruh
         | provinsi, karena nama di luar provinsi ini pun tetap nama yang ada.
         */
        $destinasi = collect($katalog['destinasi'] ?? [])
            ->when($this->isian['provinsi'] ?? '', fn ($c, $p) => $c->where('provinsi', $p))
            ->pluck('nama')->values()->all();

        return view('livewire.pages.admin.orcha.rab.master-harga-borang', [
            'destinasi' => $destinasi,
            'namaKatalog' => collect($katalog['destinasi'] ?? [])->pluck('nama')->all(),
            'vendorAda' => $katalog['vendor'] ?? [],
            'kosakata' => $katalog['kosakata'] ?? [],
            'provinsiAda' => $katalog['provinsi'] ?? [],
            /*
             | Musim yang berlaku, satu kotak tarif masing-masing.
             |
             | Ikut katalog, jadi tidak ada panggilan tambahan tiap borang
             | digambar — dan borang ini digambar ulang pada setiap ketikan.
             | Katalog disimpan 10 menit, karena itu layar Musim Harga
             | membuangnya sesudah menyimpan: musim yang baru dibuat harus
             | punya kotaknya SEKARANG, bukan sepuluh menit lagi.
             */
            'musimAda' => $katalog['musim'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
