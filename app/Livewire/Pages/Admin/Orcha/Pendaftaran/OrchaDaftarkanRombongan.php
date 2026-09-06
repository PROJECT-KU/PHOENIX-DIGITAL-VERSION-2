<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Concerns\MembacaDaftarPeserta;
use Livewire\Component;
use Livewire\WithFileUploads;

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
    use IsianRupiah, MemanggilOrcha, MembacaDaftarPeserta, WithFileUploads;

    public string $paketId = '';

    public string $nama = '';

    public string $whatsapp = '';

    public string $email = '';

    public $jumlahPeserta = 1;

    /**
     * Guru pendamping yang ikut berangkat tanpa dibayar.
     *
     * Dua angka yang memang berbeda: jumlah peserta menjawab berapa orang
     * BERANGKAT — kursi bus, manifes, riwayat kesehatan — dan angka ini
     * menjawab berapa di antaranya TIDAK DITAGIH.
     *
     * Sebelum ada kolomnya, satu-satunya cara menyatakannya adalah menurunkan
     * jumlah peserta, dan gurunya lalu hilang dari ketiga hal di atas.
     */
    public $pendampingGratis = 0;

    /**
     * Bentuk bertitik yang tampil di layar; angkanya diambil saat menyimpan.
     *
     * Memakai pola yang sudah dipakai formulir Paket Wisata — trait
     * IsianRupiah plus kelas .orcha-rupiah yang menaruh "Rp" DI DALAM
     * isiannya. Sebelumnya layar ini memasang kotak "Rp" terpisah bergaya
     * Bootstrap dan angkanya tanpa pemisah ribuan: 140000000 di layar, dan
     * yang membacanya harus menghitung nolnya dengan jari.
     */
    public string $hargaJual = '';

    public string $hargaModal = '';

    /**
     * Biaya yang TIDAK ikut bertambah saat pesertanya bertambah.
     *
     * Carter bus, guide, sopir, tol. Punya kolomnya sendiri karena memaksanya
     * masuk ke "modal per orang" menuntut admin membagi sendiri tiap kali —
     * dan yang benar-benar terjadi adalah ia memakai ulang angka rombongan
     * sebelumnya, lalu rombongan bertiga dilaporkan untung besar padahal satu
     * busnya saja lebih mahal daripada seluruh omzetnya.
     */
    public string $biayaTetap = '';

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

    /*
     | Diformat ulang saat isiannya ditinggalkan, bukan saat diketik.
     |
     | Memformat tiap ketukan membuat kursor melompat ke ujung setiap kali
     | pemisah ribuan bertambah — dan yang membetulkan satu digit di tengah
     | angka harus mengetik ulang seluruhnya.
     */
    public function updatedHargaJual(): void
    {
        $this->hargaJual = $this->keRupiah($this->angkaDari($this->hargaJual));
    }

    public function updatedHargaModal(): void
    {
        $this->hargaModal = $this->keRupiah($this->angkaDari($this->hargaModal));
    }

    public function updatedBiayaTetap(): void
    {
        $this->biayaTetap = $this->keRupiah($this->angkaDari($this->biayaTetap));
    }

    /**
     * Modal sesungguhnya per kepala — biaya tetap sudah dibagi rata.
     *
     * Ditampilkan hidup selagi admin mengetik, karena inilah angka yang
     * menentukan apakah harganya masuk akal. Rombongan bertiga dengan carter
     * Rp 3.000.000 menanggung sejuta per kepala di luar biaya per orangnya,
     * dan tanpa baris ini angka itu tidak muncul di mana pun sampai laporan
     * keuntungan dibuka berbulan-bulan kemudian.
     */
    public function modalPerKepala(): int
    {
        $orang = max(1, (int) $this->jumlahPeserta);

        return (int) round(
            ($this->angkaDari($this->hargaModal) * $orang + $this->angkaDari($this->biayaTetap)) / $orang
        );
    }

    /** Untung yang diperkirakan dari angka yang sedang diketik. */
    public function perkiraanUntung(): int
    {
        return $this->totalTagihan()
            - $this->angkaDari($this->hargaModal) * max(1, (int) $this->jumlahPeserta)
            - $this->angkaDari($this->biayaTetap);
    }

    /** Total yang benar-benar ditagihkan, dipakai layar sebagai ringkasan. */
    public function totalTagihan(): int
    {
        $ditagih = max(0, (int) $this->jumlahPeserta - (int) ($this->pendampingGratis ?: 0));

        return $this->angkaDari($this->hargaJual) * $ditagih;
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
        $this->pakaiDaftar($this->uraikanTempelan($teks));
    }

    /**
     * Berkas Excel/CSV panitia.
     *
     * Daftar study tour biasanya sudah berbentuk berkas sejak awal — dikirim
     * panitia sebagai lampiran, bukan diketik di badan pesan. Menyuruh admin
     * membukanya lalu menyalin isinya ke kotak tempelan hanya memindahkan
     * pekerjaan, dan pada empat puluh baris pekerjaan itu cukup melelahkan
     * untuk akhirnya dilewati.
     */
    public $berkasPeserta;

    public function updatedBerkasPeserta(): void
    {
        $this->validate([
            'berkasPeserta' => 'file|mimes:xlsx,xls,csv,txt|max:2048',
        ], [], ['berkasPeserta' => 'berkas peserta']);

        $baris = $this->bacaBerkasPeserta($this->berkasPeserta);
        $this->berkasPeserta = null;

        if ($baris === null) {
            $this->dispatch('toast-error',
                message: 'Berkas itu tidak bisa dibaca. Coba simpan ulang sebagai CSV.');

            return;
        }

        if ($baris === []) {
            $this->dispatch('toast-error', message: 'Tidak ada nama yang terbaca di berkas itu.');

            return;
        }

        $this->pakaiDaftar($baris);
    }

    /**
     * Memakai hasil uraian sebagai daftar peserta.
     *
     * @param  array<int, array<string, mixed>>  $baris
     */
    private function pakaiDaftar(array $baris): void
    {
        if ($baris === []) {
            return;
        }

        $this->peserta = collect($baris)
            ->map(fn ($satu) => [
                'nama' => $satu['nama'],
                // Titik jemput utama dipakai hanya bila barisnya sendiri tidak
                // menyebutkannya. Rombongan sekolah berangkat dari satu titik,
                // tetapi sebagian daftar memang menyebut titik per orang.
                'titik_jemput' => $satu['titik_jemput'] !== '' ? $satu['titik_jemput'] : $this->titikJemput,
            ])
            ->all();

        /*
         | Jumlah pesertanya ikut menyesuaikan.
         |
         | Angka itulah yang mengalikan harga jadi tagihan. Membiarkannya
         | tertinggal di angka lama setelah empat puluh nama ditempel berarti
         | rombongan empat puluh orang ditagih untuk satu orang — dan yang
         | menemukannya nanti bukan kita.
         */
        $this->jumlahPeserta = count($this->peserta);
    }

    public function simpan(): void
    {
        $this->validate([
            'paketId' => 'required',
            'nama' => 'required|string|min:3|max:120',
            'whatsapp' => 'required|string|min:8|max:32',
            'email' => 'nullable|email|max:150',
            'jumlahPeserta' => 'required|integer|min:1|max:200',
            // Rombongan yang seluruhnya gratis berarti tidak ada yang membayar
            // apa pun — itu bukan pendaftaran melainkan salah ketik.
            'pendampingGratis' => 'nullable|integer|min:0|lt:jumlahPeserta',
            // Bentuknya bertitik, jadi yang divalidasi angkanya — bukan
            // teksnya, yang tidak akan pernah lolos aturan numeric.
            'hargaJual' => 'nullable|string|max:20',
            'hargaModal' => 'nullable|string|max:20',
            'biayaTetap' => 'nullable|string|max:20',
            'peserta.*.nama' => 'nullable|string|max:120',
        ], [], [
            'paketId' => 'paket',
            'nama' => 'nama pemesan',
            'whatsapp' => 'nomor WhatsApp',
            'jumlahPeserta' => 'jumlah peserta',
            'pendampingGratis' => 'pendamping gratis',
            'hargaJual' => 'harga per orang',
            'hargaModal' => 'modal per orang',
            'biayaTetap' => 'biaya tetap rombongan',
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
                'pendamping_gratis' => (int) ($this->pendampingGratis ?: 0),
                'peserta' => $isi,
                'titik_jemput' => $this->titikJemput ?: null,
                'catatan' => $this->catatan ?: null,
                'harga_jual' => $this->hargaJual !== '' ? $this->angkaDari($this->hargaJual) : null,
                'harga_modal' => $this->hargaModal !== '' ? $this->angkaDari($this->hargaModal) : null,
                'biaya_tetap' => $this->angkaDari($this->biayaTetap),
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
        // berkasPeserta ikut dikosongkan: berkas yang tertinggal akan terbaca
        // lagi saat Livewire menggambar ulang, dan daftar rombongan sebelumnya
        // muncul di rombongan berikutnya.
        $this->reset(['paketId', 'nama', 'whatsapp', 'email', 'jumlahPeserta',
            'pendampingGratis', 'hargaJual', 'hargaModal', 'biayaTetap', 'titikJemput', 'catatan',
            'berkasPeserta', 'hasil']);

        $this->jumlahPeserta = 1;
        $this->pendampingGratis = 0;
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
