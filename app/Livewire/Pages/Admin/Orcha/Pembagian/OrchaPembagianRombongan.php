<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembagian;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Support\PembagianPeriksa;
use Livewire\Component;

/**
 * Membagi kursi kendaraan dan kamar untuk SATU ROMBONGAN.
 *
 * Satuannya rombongan, bukan pendaftaran — dan itu yang membuat layar ini
 * perlu ada sendiri. Satu bus mengangkut peserta dari banyak pemesanan
 * sekaligus, dan satu kamar twin diisi dua orang yang bisa saja mendaftar
 * terpisah. Dikerjakan dari halaman pendaftaran, dua admin yang membuka dua
 * pemesanan berbeda akan sama-sama menaruh orangnya di "Bus 1 kursi 5" tanpa
 * bisa melihat bentrokannya; yang pertama tahu adalah orang yang berdiri di
 * lorong bus.
 *
 * Cara memakainya dua ketukan, bukan seret-lepas: pilih orangnya, lalu ketuk
 * kursi atau kamarnya. Seret-lepas tidak bisa dipakai di ponsel, dan
 * pembagian ini justru sering dikerjakan sambil berdiri — menelepon hotel di
 * tangan satu dan daftar peserta di tangan lain.
 *
 * Perubahan ditahan di layar sampai Simpan ditekan, dan selama itu DITANDAI
 * belum tersimpan. Menyimpan setiap ketukan berarti satu panggilan ke Orcha
 * per orang untuk pekerjaan yang menyentuh seratus orang; sedangkan menahan
 * tanpa menandai berarti admin menutup tab dan kehilangan satu jam kerja
 * tanpa pernah tahu ia belum menyimpan.
 */
class OrchaPembagianRombongan extends Component
{
    use MemanggilOrcha;

    public int $keberangkatanId;

    /** Keadaan dari Orcha: keberangkatan, kendaraan, kamar, petak, peserta. */
    public array $data = [];

    /**
     * Penempatan yang sedang disusun, kunci peserta => medannya.
     *
     * Dipisahkan dari $data supaya yang BELUM disimpan bisa dibedakan dari yang
     * sudah. Ditulis langsung ke $data, layar jadi tidak punya cara
     * memberitahu admin bahwa pekerjaannya masih di layar saja.
     *
     * @var array<string, array<string, ?string>>
     */
    public array $ubahan = [];

    /** Peserta yang sedang dipilih; ketukan berikutnya menempatkannya. */
    public string $terpilih = '';

    /** Panel pendaftaran kendaraan & kamar, tertutup secara bawaan. */
    public bool $bukaFasilitas = false;

    /** @var array<int, array<string, mixed>> */
    public array $kendaraan = [];

    /** @var array<int, array<string, mixed>> */
    public array $kamar = [];

    /** Pembagian otomatis boleh menimpa yang sudah dibuat tangan? */
    public bool $timpa = false;

    /** Laporan pembagian otomatis terakhir: sisa dan catatan. */
    public array $laporan = [];

    public function mount(int $keberangkatan): void
    {
        $this->keberangkatanId = $keberangkatan;
        $this->muatKeadaan();
    }

    private function muatKeadaan(): void
    {
        $this->data = $this->muat("/keberangkatan/{$this->keberangkatanId}/pembagian")['data'] ?? [];
        $this->kendaraan = $this->data['kendaraan'] ?? [];
        $this->kamar = $this->data['kamar'] ?? [];
        $this->ubahan = [];
        $this->terpilih = '';
    }

    /**
     * Peserta rombongan ini, sudah menggabungkan perubahan yang belum
     * tersimpan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function peserta(): array
    {
        return collect($this->data['peserta'] ?? [])
            ->map(function ($satu) {
                $kunci = (string) $satu['kunci'];

                if (! array_key_exists($kunci, $this->ubahan)) {
                    $satu['berubah'] = false;

                    return $satu;
                }

                $satu = array_merge($satu, $this->ubahan[$kunci]);
                $satu['berubah'] = true;

                return $satu;
            })
            ->all();
    }

    public function adaUbahan(): bool
    {
        return $this->ubahan !== [];
    }

    /**
     * Memilih atau membatalkan pilihan satu peserta.
     *
     * Menekan yang sudah terpilih membatalkannya. Tanpa itu, pilihan yang
     * menempel membuat ketukan berikutnya di mana pun memindahkan orang yang
     * admin sudah lupa sedang dipegangnya.
     */
    public function pilih(string $kunci): void
    {
        $this->terpilih = $this->terpilih === $kunci ? '' : $kunci;
    }

    /**
     * Menempatkan yang terpilih ke satu kursi.
     *
     * Kursi yang SUDAH berpenghuni ditukar isinya, bukan ditumpuk. Menumpuk
     * menghasilkan dua orang di satu kursi — keadaan yang tidak pernah sah, dan
     * yang akibatnya baru terasa setelah bus berjalan. Menolak juga salah:
     * admin yang menukar dua orang pasti melewati keadaan itu di tengah jalan.
     */
    public function tempatkanKursi(string $bus, string $kursi): void
    {
        if ($this->terpilih === '') {
            $this->dispatch('toast-error',
                message: 'Pilih dulu nama pesertanya di daftar sebelah, baru ketuk kursinya.');

            return;
        }

        $semua = collect($this->peserta());
        $penghuni = $semua->first(
            fn ($satu) => (string) ($satu['bus'] ?? '') === $bus
                && (string) ($satu['kursi'] ?? '') === $kursi
        );
        $pindah = $semua->firstWhere('kunci', $this->terpilih);

        // Orang yang menempati kursi tujuan mengambil kursi asal orang yang
        // dipindahkan — pertukaran, bukan penggusuran.
        if ($penghuni && (string) $penghuni['kunci'] !== $this->terpilih) {
            $this->catatUbahan((string) $penghuni['kunci'], [
                'bus' => (string) ($pindah['bus'] ?? '') ?: null,
                'kursi' => (string) ($pindah['kursi'] ?? '') ?: null,
            ]);
        }

        $this->catatUbahan($this->terpilih, ['bus' => $bus, 'kursi' => $kursi]);
        $this->terpilih = '';
    }

    /** Mengosongkan satu kursi tanpa memindahkan siapa pun ke sana. */
    public function kosongkanKursi(string $kunci): void
    {
        $this->catatUbahan($kunci, ['bus' => null, 'kursi' => null]);
    }

    /**
     * Menempatkan yang terpilih ke satu kamar.
     *
     * Kamar yang sudah penuh DITOLAK, berbeda dari kursi. Kapasitas kamar
     * adalah janji ke hotel, bukan tata letak yang bisa dipadatkan: kamar twin
     * berisi tiga orang berarti satu orang tidur di lantai, dan yang mengetahui
     * itu resepsionis hotel pada jam sebelas malam.
     */
    public function tempatkanKamar(string $nama): void
    {
        if ($this->terpilih === '') {
            $this->dispatch('toast-error',
                message: 'Pilih dulu nama pesertanya di daftar sebelah, baru ketuk kamarnya.');

            return;
        }

        $kapasitas = (int) (collect($this->kamar)
            ->firstWhere('nama', $nama)['kapasitas'] ?? 0);

        $penghuni = collect($this->peserta())
            ->filter(fn ($satu) => (string) ($satu['kamar'] ?? '') === $nama
                && (string) $satu['kunci'] !== $this->terpilih);

        if ($kapasitas > 0 && $penghuni->count() >= $kapasitas) {
            $this->dispatch('toast-error',
                message: 'Kamar '.$nama.' sudah terisi '.$penghuni->count().' dari '
                    .$kapasitas.' orang. Keluarkan salah satunya dulu, atau pakai kamar lain.');

            return;
        }

        $this->catatUbahan($this->terpilih, ['kamar' => $nama]);
        $this->terpilih = '';
    }

    public function kosongkanKamar(string $kunci): void
    {
        $this->catatUbahan($kunci, ['kamar' => null]);
    }

    /**
     * Mengisi jenis kelamin dari layar ini.
     *
     * Ada di sini, bukan cuma di layar peserta, karena di sinilah
     * kekurangannya terasa: pembagian kamar yang berhenti karena tiga orang
     * belum diisi jenis kelaminnya memaksa admin berpindah ke tiga pendaftaran
     * berbeda lalu kembali — dan yang terjadi berikutnya bukan ia melengkapi
     * data, melainkan ia membagi kamarnya dengan tangan.
     */
    public function setKelamin(string $kunci, string $kelamin): void
    {
        if (! in_array($kelamin, ['Laki-laki', 'Perempuan', ''], true)) {
            return;
        }

        $this->catatUbahan($kunci, ['jenis_kelamin' => $kelamin ?: null]);
    }

    /**
     * Menyimpan jenis kelamin yang datang dari borang riwayat kesehatan.
     *
     * Layar sudah MENAMPILKAN nilai itu — Orcha menggabungkannya saat memuat —
     * tetapi yang tersimpan di baris pesertanya masih kosong. Selama begitu,
     * pembagiannya bergantung pada kaitan lewat NAMA: begitu ejaan nama
     * diperbaiki, jenis kelaminnya hilang lagi dan kamar yang sudah dibagi
     * terbaca sebagai "belum diisi".
     *
     * Sekali tekan untuk seluruh rombongan. Tanpa ini, satu-satunya jalan
     * adalah menekan L atau P enam puluh kali untuk data yang sudah ada.
     */
    public function ambilKelaminDariKesehatan(): void
    {
        $dari = collect($this->peserta())
            ->filter(fn ($satu) => ($satu['kelamin_dari_kesehatan'] ?? false)
                && filled($satu['jenis_kelamin'] ?? null));

        if ($dari->isEmpty()) {
            $this->dispatch('toast-error',
                message: 'Tidak ada yang bisa diambil — tidak ada peserta yang jenis kelaminnya '
                    .'hanya diketahui dari formulir kesehatan.');

            return;
        }

        foreach ($dari as $satu) {
            $this->catatUbahan((string) $satu['kunci'], ['jenis_kelamin' => $satu['jenis_kelamin']]);
        }

        $this->dispatch('order-updated',
            message: $dari->count().' jenis kelamin diambil dari formulir kesehatan. '
                .'Tekan Simpan supaya tidak hilang lagi saat nama diperbaiki.');
    }

    /** @param  array<string, ?string>  $medan */
    private function catatUbahan(string $kunci, array $medan): void
    {
        $this->ubahan[$kunci] = array_merge($this->ubahan[$kunci] ?? [], $medan);
    }

    public function batalkanUbahan(): void
    {
        $this->ubahan = [];
        $this->terpilih = '';
        $this->dispatch('order-updated', message: 'Perubahan yang belum tersimpan dibuang.');
    }

    public function simpan(): void
    {
        if ($this->ubahan === []) {
            $this->dispatch('toast-error', message: 'Belum ada yang diubah.');

            return;
        }

        /*
         | Yang dikirim HANYA baris yang berubah, tetapi tiap baris dikirim
         | LENGKAP keempat medannya.
         |
         | Jalur Orcha memperbarui medan yang disebut saja. Mengirim separuh
         | medan berarti mengosongkan kamar seseorang tidak pernah sampai —
         | ia terbaca sebagai "kamar tidak disebut", bukan "kamar dikosongkan".
         */
        $kirim = collect($this->peserta())
            ->filter(fn ($satu) => $satu['berubah'])
            ->map(fn ($satu) => [
                'kunci' => (string) $satu['kunci'],
                'bus' => (string) ($satu['bus'] ?? ''),
                'kursi' => (string) ($satu['kursi'] ?? ''),
                'kamar' => (string) ($satu['kamar'] ?? ''),
                'jenis_kelamin' => (string) ($satu['jenis_kelamin'] ?? ''),
            ])
            ->values()
            ->all();

        try {
            $balasan = $this->orcha()->ubah(
                "/keberangkatan/{$this->keberangkatanId}/pembagian",
                ['peserta' => $kirim],
            );

            $this->data = array_merge($this->data, $balasan['data'] ?? []);
            $this->ubahan = [];
            $this->terpilih = '';
            $this->laporan = [];

            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Pembagian tersimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Membagi otomatis lalu memuat hasilnya.
     *
     * Disimpan langsung di Orcha, tidak ditahan di layar seperti ketukan
     * tangan. Alasannya: yang dihasilkannya puluhan baris sekaligus, dan
     * "belum disimpan" atas puluhan baris tidak bisa diperiksa admin satu per
     * satu sebelum memutuskan. Yang bisa diperiksa justru HASILNYA — dan untuk
     * itu ia harus sudah tergambar di petak dan papan kamar.
     */
    /**
     * Mengembalikan pembagian ke potret sebelumnya.
     *
     * Yang dikembalikan penempatannya, bukan daftar siapa yang ikut: peserta
     * yang pendaftarannya dibatalkan sesudah potret dibuat tidak dihidupkan
     * lagi oleh ini.
     */
    public function kembalikan(int $urutan): void
    {
        if ($this->ubahan !== []) {
            $this->dispatch('toast-error',
                message: 'Simpan atau buang dulu perubahan yang belum tersimpan.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim(
                "/keberangkatan/{$this->keberangkatanId}/pembagian/kembalikan",
                ['urutan' => $urutan],
            );

            $this->data = array_merge($this->data, $balasan['data'] ?? []);
            $this->laporan = [];
            $this->terpilih = '';

            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Pembagian dikembalikan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function bagiOtomatis(string $apa): void
    {
        if ($this->ubahan !== []) {
            $this->dispatch('toast-error',
                message: 'Simpan atau buang dulu perubahan yang belum tersimpan, '
                    .'supaya tidak bercampur dengan hasil pembagian otomatis.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim(
                "/keberangkatan/{$this->keberangkatanId}/pembagian/otomatis",
                ['apa' => $apa, 'timpa' => $this->timpa ? 1 : 0],
            );

            $this->data = array_merge($this->data, $balasan['data'] ?? []);
            $this->laporan = [
                'sisa' => $balasan['data']['sisa'] ?? [],
                'catatan' => $balasan['data']['catatan'] ?? [],
            ];

            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Pembagian dikerjakan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Kendaraan & kamar yang disiapkan
     |--------------------------------------------------------------------------
     |
     | Dipisahkan dari penempatan orangnya. Armada dan blok kamar ditentukan
     | sekali saat dipesan ke vendor; penempatan orangnya berubah sampai hari
     | terakhir. Satu borang untuk keduanya berarti setiap pergeseran satu orang
     | mengirim ulang seluruh daftar armada, dan satu kesalahan di sana
     | menghapus kendaraan yang pesertanya sudah ditempatkan.
     */

    public function tambahKendaraan(): void
    {
        $this->kendaraan[] = [
            'nama' => 'Bus '.(count($this->kendaraan) + 1),
            'tata' => '2-2',
            'jumlah_kursi' => 44,
        ];
    }

    public function hapusKendaraan(int $urutan): void
    {
        unset($this->kendaraan[$urutan]);
        $this->kendaraan = array_values($this->kendaraan);
    }

    public function tambahKamar(): void
    {
        $this->kamar[] = ['nama' => '', 'kapasitas' => 2];
    }

    public function hapusKamar(int $urutan): void
    {
        unset($this->kamar[$urutan]);
        $this->kamar = array_values($this->kamar);
    }

    /**
     * Membuat sederet kamar bernomor sekaligus.
     *
     * Hotel memberi blok kamar berurutan ("201 sampai 215"), dan mengetiknya
     * satu per satu lima belas kali adalah pekerjaan yang paling sering
     * membuat fitur ini tidak dipakai sama sekali.
     */
    public string $deretAwal = '';

    public int $deretJumlah = 10;

    public int $deretKapasitas = 2;

    public function tambahDeretKamar(): void
    {
        $awal = trim($this->deretAwal);

        if (! preg_match('/^(\D*)(\d+)$/u', $awal, $cocok)) {
            $this->dispatch('toast-error',
                message: 'Nomor awalnya harus berakhiran angka, misalnya 201 atau A-201.');

            return;
        }

        [, $awalan, $angka] = $cocok;
        $jumlah = max(1, min(60, $this->deretJumlah));
        $lebar = strlen($angka);

        $sudahAda = collect($this->kamar)
            ->map(fn ($satu) => mb_strtolower(trim((string) ($satu['nama'] ?? ''))))
            ->all();

        $ditambah = 0;

        for ($i = 0; $i < $jumlah; $i++) {
            // Nol di depan dipertahankan: hotel yang memberi "012" memberi
            // nomor pintu, bukan bilangan — dan "12" adalah pintu lain.
            $nama = $awalan.str_pad((string) ((int) $angka + $i), $lebar, '0', STR_PAD_LEFT);

            if (in_array(mb_strtolower($nama), $sudahAda, true)) {
                continue;
            }

            $this->kamar[] = ['nama' => $nama, 'kapasitas' => max(1, min(12, $this->deretKapasitas))];
            $sudahAda[] = mb_strtolower($nama);
            $ditambah++;
        }

        $this->deretAwal = '';

        $this->dispatch('order-updated', message: $ditambah === $jumlah
            ? $ditambah.' kamar ditambahkan. Jangan lupa Simpan Kendaraan & Kamar.'
            : $ditambah.' kamar ditambahkan; sisanya sudah ada di daftar.');
    }

    public function simpanFasilitas(): void
    {
        $kendaraan = collect($this->kendaraan)
            ->map(fn ($satu) => [
                'nama' => trim((string) ($satu['nama'] ?? '')),
                'tata' => (string) ($satu['tata'] ?? '2-2'),
                'jumlah_kursi' => (int) ($satu['jumlah_kursi'] ?? 0),
            ])
            ->filter(fn ($satu) => $satu['nama'] !== '' && $satu['jumlah_kursi'] > 0)
            ->values()
            ->all();

        $kamar = collect($this->kamar)
            ->map(fn ($satu) => [
                'nama' => trim((string) ($satu['nama'] ?? '')),
                'kapasitas' => (int) ($satu['kapasitas'] ?? 0),
            ])
            ->filter(fn ($satu) => $satu['nama'] !== '' && $satu['kapasitas'] > 0)
            ->values()
            ->all();

        try {
            $balasan = $this->orcha()->ubah(
                "/keberangkatan/{$this->keberangkatanId}/fasilitas",
                ['kendaraan' => $kendaraan, 'kamar' => $kamar],
            );

            $this->data['kendaraan'] = $balasan['data']['kendaraan'] ?? $kendaraan;
            $this->data['kamar'] = $balasan['data']['kamar'] ?? $kamar;
            $this->data['petak'] = $balasan['data']['petak'] ?? ($this->data['petak'] ?? []);
            $this->kendaraan = $this->data['kendaraan'];
            $this->kamar = $this->data['kamar'];

            $this->dispatch('order-updated',
                message: $balasan['pesan'] ?? 'Kendaraan dan kamar tersimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Ringkasan yang dibaca ulang dari keadaan di layar, bukan dari Orcha.
     *
     * Angka dari Orcha benar untuk data yang TERSIMPAN; yang sedang dilihat
     * admin termasuk yang belum. Dua angka yang berbeda di satu layar membuat
     * yang membacanya berhenti percaya keduanya.
     */
    public function ringkasan(): array
    {
        $peserta = collect($this->peserta());

        return [
            'total_peserta' => $peserta->count(),
            'laki' => $peserta->where('jenis_kelamin', 'Laki-laki')->count(),
            'perempuan' => $peserta->where('jenis_kelamin', 'Perempuan')->count(),
            'belum_jenis_kelamin' => $peserta->filter(
                fn ($satu) => ! in_array($satu['jenis_kelamin'] ?? null, ['Laki-laki', 'Perempuan'], true)
            )->count(),
            'sudah_kursi' => $peserta->filter(fn ($satu) => filled($satu['kursi'] ?? null))->count(),
            'sudah_kamar' => $peserta->filter(fn ($satu) => filled($satu['kamar'] ?? null))->count(),
            'jumlah_kursi_tersedia' => collect($this->data['kendaraan'] ?? [])->sum('jumlah_kursi'),
            'daya_tampung_kamar' => collect($this->data['kamar'] ?? [])->sum('kapasitas'),
        ];
    }

    /**
     * Peringatan dihitung ULANG di lemon, bukan dibaca dari Orcha.
     *
     * Sebabnya sama dengan ringkasan: yang sedang disusun admin belum terkirim
     * ke sana. Peringatan yang terlambat satu langkah adalah peringatan yang
     * menyala setelah kesalahannya tersimpan.
     *
     * @return array<int, array{jenis: string, pesan: string}>
     */
    public function peringatan(): array
    {
        return PembagianPeriksa::periksa($this->peserta());
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.pembagian.index', [
            'keberangkatan' => $this->data['keberangkatan'] ?? [],
            'petak' => $this->data['petak'] ?? [],
            'pilihanTata' => $this->data['pilihan_tata'] ?? [],
            'peserta' => $this->peserta(),
            'ringkasan' => $this->ringkasan(),
            'peringatan' => $this->peringatan(),
            'riwayat' => $this->data['riwayat'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
