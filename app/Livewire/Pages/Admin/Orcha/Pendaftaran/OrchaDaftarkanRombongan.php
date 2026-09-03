<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Mendaftarkan rombongan private trip dan study tour.
 *
 * Keduanya tidak pernah mendaftar lewat website, dan itu bukan kekurangan
 * melainkan bentuk jualannya: harganya dirundingkan, jumlah pesertanya berubah
 * sampai menit terakhir, dan seluruh percakapannya terjadi di WhatsApp.
 *
 * Tetapi begitu disepakati, rombongannya HARUS masuk sistem. Tanpa itu ia
 * tidak punya kode pemesanan, tidak bisa mengisi riwayat kesehatan, tidak
 * masuk manifes tour leader, dan tidak terhitung di laporan keuntungan —
 * empat hal yang baru terasa hilang justru saat rombongannya sudah berkumpul.
 *
 * Layar ini berakhir pada satu hal yang bisa langsung dikirim: kode pemesanan
 * beserta tautan riwayat kesehatannya, sudah tersusun jadi pesan WhatsApp.
 * Menyalinnya sendiri berarti admin mengetik ulang kode enam huruf acak, dan
 * kode yang salah satu huruf membawa panitia ke halaman yang menolaknya.
 */
class OrchaDaftarkanRombongan extends Component
{
    use MemanggilOrcha;

    public string $paketId = '';

    public string $nama = '';

    public string $whatsapp = '';

    public string $email = '';

    public $jumlahPeserta = 1;

    public string $hargaJual = '';

    public string $hargaModal = '';

    public string $titikJemput = '';

    public string $catatan = '';

    /** @var array<int, array{nama: string, titik_jemput: string}> */
    public array $peserta = [];

    /** Hasil pendaftaran; terisi berarti layarnya sudah berpindah ke serah terima. */
    public array $hasil = [];

    public function mount(): void
    {
        $this->peserta = [['nama' => '', 'titik_jemput' => '']];
    }

    public function tambahBaris(): void
    {
        $this->peserta[] = ['nama' => '', 'titik_jemput' => ''];
    }

    public function hapusBaris(int $nomor): void
    {
        unset($this->peserta[$nomor]);
        $this->peserta = array_values($this->peserta);

        if ($this->peserta === []) {
            $this->peserta = [['nama' => '', 'titik_jemput' => '']];
        }
    }

    /**
     * Menempelkan daftar nama dari WhatsApp atau berkas panitia.
     *
     * Daftar peserta study tour datang sebagai satu blok teks — satu nama per
     * baris, kadang bernomor. Mengetiknya ulang satu per satu untuk empat
     * puluh siswa adalah pekerjaan yang membuat layar ini tidak dipakai sama
     * sekali, dan rombongannya kembali dicatat di kertas.
     */
    public function tempel(string $teks): void
    {
        $nama = collect(preg_split('/\r\n|\r|\n/', $teks))
            // Nomor urut di depan ikut dibuang: "1. Budi", "1) Budi", "1 Budi".
            ->map(fn ($baris) => trim(preg_replace('/^\s*\d+\s*[.)\-]?\s*/', '', $baris)))
            ->filter(fn ($baris) => $baris !== '')
            ->values();

        if ($nama->isEmpty()) {
            return;
        }

        $this->peserta = $nama
            ->map(fn ($satu) => ['nama' => $satu, 'titik_jemput' => $this->titikJemput])
            ->all();

        /*
         | Jumlah pesertanya ikut menyesuaikan.
         |
         | Angka itulah yang mengalikan harga jadi tagihan. Membiarkannya
         | tertinggal di angka lama setelah empat puluh nama ditempel berarti
         | rombongan empat puluh orang ditagih untuk satu orang — dan yang
         | menemukannya nanti bukan kita.
         */
        $this->jumlahPeserta = $nama->count();
    }

    public function simpan(): void
    {
        $this->validate([
            'paketId' => 'required',
            'nama' => 'required|string|min:3|max:120',
            'whatsapp' => 'required|string|min:8|max:32',
            'email' => 'nullable|email|max:150',
            'jumlahPeserta' => 'required|integer|min:1|max:200',
            'hargaJual' => 'nullable|numeric|min:0',
            'hargaModal' => 'nullable|numeric|min:0',
            'peserta.*.nama' => 'nullable|string|max:120',
        ], [], [
            'paketId' => 'paket',
            'nama' => 'nama pemesan',
            'whatsapp' => 'nomor WhatsApp',
            'jumlahPeserta' => 'jumlah peserta',
            'hargaJual' => 'harga per orang',
            'hargaModal' => 'modal per orang',
        ]);

        $isi = collect($this->peserta)
            ->filter(fn ($baris) => trim($baris['nama'] ?? '') !== '')
            ->map(fn ($baris) => [
                'nama' => trim($baris['nama']),
                'titik_jemput' => trim($baris['titik_jemput'] ?? '') ?: null,
            ])
            ->values()
            ->all();

        /*
         | Nama yang lebih banyak daripada jumlah peserta DITAHAN di sini.
         |
         | Orcha menerima keduanya apa adanya — ia tidak berhak menebak mana
         | yang benar. Tetapi selisihnya hampir selalu berarti admin menempel
         | daftar lalu lupa menyesuaikan angkanya, dan akibatnya tagihan yang
         | tidak sesuai dengan orang yang berangkat. Ditahan di sini, sebelum
         | siapa pun menerima kode pemesanan yang salah.
         */
        if (count($isi) > (int) $this->jumlahPeserta) {
            $this->addError('jumlahPeserta',
                'Ada '.count($isi).' nama peserta tetapi jumlahnya diisi '.$this->jumlahPeserta
                .'. Samakan dulu — angka inilah yang mengalikan harga jadi tagihan.');

            return;
        }

        try {
            $hasil = $this->orcha()->kirim('/pendaftaran', array_filter([
                'travel_package_id' => (int) $this->paketId,
                'nama' => $this->nama,
                'whatsapp' => $this->whatsapp,
                'email' => $this->email ?: null,
                'jumlah_peserta' => (int) $this->jumlahPeserta,
                'peserta' => $isi,
                'titik_jemput' => $this->titikJemput ?: null,
                'catatan' => $this->catatan ?: null,
                'harga_jual' => $this->hargaJual !== '' ? (int) $this->hargaJual : null,
                'harga_modal' => $this->hargaModal !== '' ? (int) $this->hargaModal : null,
            ], fn ($nilai) => $nilai !== null));

            $this->hasil = $hasil['data'] ?? [];

            $this->dispatch('order-updated', message: 'Rombongan terdaftar dengan kode '
                .($this->hasil['kode'] ?? '').'.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /** Mengosongkan layar untuk rombongan berikutnya. */
    public function lagi(): void
    {
        $this->reset(['paketId', 'nama', 'whatsapp', 'email', 'jumlahPeserta',
            'hargaJual', 'hargaModal', 'titikJemput', 'catatan', 'hasil']);

        $this->jumlahPeserta = 1;
        $this->peserta = [['nama' => '', 'titik_jemput' => '']];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.pendaftaran.daftarkan', [
            'pilihanPaket' => $this->rujukan('paket_wisata'),
        ])->layout('livewire.layout.templateindex');
    }
}
