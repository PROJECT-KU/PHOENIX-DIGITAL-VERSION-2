<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Melengkapi nama peserta sebuah pendaftaran.
 *
 * Halaman tersendiri, bukan jendela di atas halaman detail. Dua alasan:
 * rombongan bisa berisi puluhan orang dan daftar sepanjang itu sesak di dalam
 * jendela kecil, dan membukanya cukup berpindah halaman biasa — tidak menunggu
 * panggilan latar yang bila server sedang sibuk tampak seperti tombol yang
 * tidak bereaksi sama sekali.
 *
 * Pendaftaran lama tidak menyimpan nama peserta satu per satu, dan rombongan
 * tanpa nama tidak bisa masuk manifes panggil-nama. Menunggu pemesan mengisi
 * ulang lewat website jarang berhasil — daftarnya biasanya sudah ada di tangan
 * panitia, hanya belum masuk sistem.
 */
class OrchaPesertaForm extends Component
{
    use MemanggilOrcha, WithFileUploads;

    public int $pendaftaranId;

    public array $data = [];

    /**
     * Baris isian peserta.
     *
     * `gantikan` diisi HANYA saat admin menekan tombol Ganti: ia menyimpan nama
     * lama yang sedang digantikan baris ini. Mengetik ulang nama di isian yang
     * sama tidak mengisinya — dan memang tidak boleh, karena membetulkan salah
     * ketik bukan penggantian peserta.
     *
     * `gantikan_titik` menyimpan titik jemput orang yang digantikan, supaya
     * perpindahan titiknya terbaca — dan tercatat — bukan hanya namanya.
     *
     * @var array<int, array{nama: string, titik_jemput: string, gantikan: ?string, gantikan_titik: ?string}>
     */
    public array $barisPeserta = [];

    /** Tempelan dari Excel/WhatsApp; diuraikan jadi baris. */
    public string $tempelan = '';

    public $berkasPeserta;

    public function mount(int $pendaftaran): void
    {
        $this->pendaftaranId = $pendaftaran;

        $isi = $this->muat("/pendaftaran/{$pendaftaran}")['data'] ?? [];
        $this->data = $isi;

        $this->barisPeserta = collect($isi['peserta'] ?? [])
            ->map(fn ($satu) => [
                'nama' => (string) ($satu['nama'] ?? ''),
                'titik_jemput' => (string) ($satu['titik_jemput'] ?? ''),
                'gantikan' => null,
                'gantikan_titik' => null,
            ])
            ->all();

        if ($this->barisPeserta === []) {
            $this->barisPeserta = [$this->barisKosong()];
        }
    }

    public function tambahBaris(): void
    {
        $this->barisPeserta[] = $this->barisKosong();
    }

    public function hapusBaris(int $urutan): void
    {
        unset($this->barisPeserta[$urutan]);

        $this->barisPeserta = array_values($this->barisPeserta) ?: [$this->barisKosong()];
    }

    /**
     * Menguraikan tempelan jadi baris peserta.
     *
     * Bentuk yang datang ke admin bermacam-macam: kolom Excel yang disalin
     * (dipisah tab), daftar WhatsApp bernomor ("1. Budi Santoso"), atau nama
     * yang dipisah koma. Ketiganya diterima — memaksa admin merapikan dulu
     * berarti pekerjaan yang sama tetap dikerjakan tangan, hanya pindah tempat.
     */
    public function tempel(): void
    {
        $baris = $this->uraikan(preg_split('/\r\n|\r|\n/', $this->tempelan) ?: []);

        if ($baris === []) {
            $this->dispatch('toast-error', message: 'Tidak ada nama yang bisa dibaca dari tempelan itu.');

            return;
        }

        $this->gabungkan($baris);
        $this->tempelan = '';
    }

    /** Berkas Excel/CSV panitia, dibaca di sini lalu disimpan sebagai nama biasa. */
    public function updatedBerkasPeserta(): void
    {
        $this->validate([
            'berkasPeserta' => 'file|mimes:xlsx,xls,csv,txt|max:2048',
        ], [], ['berkasPeserta' => 'berkas peserta']);

        try {
            $lembar = Excel::toArray(new class implements ToArray
            {
                public function array(array $baris) {}
            }, $this->berkasPeserta);
        } catch (\Throwable $e) {
            $this->dispatch('toast-error', message: 'Berkas itu tidak bisa dibaca. Coba simpan ulang sebagai CSV.');
            $this->berkasPeserta = null;

            return;
        }

        $baris = $this->uraikan(collect($lembar[0] ?? [])
            ->map(fn ($kolom) => implode("\t", array_map(fn ($isi) => (string) $isi, (array) $kolom)))
            ->all(), dariBerkas: true);

        if ($baris === []) {
            $this->dispatch('toast-error', message: 'Tidak ada nama yang terbaca di berkas itu.');
            $this->berkasPeserta = null;

            return;
        }

        $this->gabungkan($baris);
        $this->berkasPeserta = null;
    }

    public function simpan(): void
    {
        $bersih = collect($this->barisPeserta)
            ->map(function ($baris) {
                $titik = trim($baris['titik_jemput'] ?? '');
                $titikLama = trim((string) ($baris['gantikan_titik'] ?? ''));

                return array_filter([
                    'nama' => trim($baris['nama'] ?? ''),
                    'titik_jemput' => $titik,
                    'gantikan' => trim((string) ($baris['gantikan'] ?? '')) ?: null,
                    // Ikut dikirim walau titiknya sama. Yang membaca arsipnya
                    // kelak tidak bisa membedakan "titiknya memang tetap" dari
                    // "titiknya tidak sempat dicatat" kalau barisnya kosong.
                    'gantikan_titik' => $titikLama ?: null,
                ], fn ($nilai) => $nilai !== null);
            })
            ->filter(fn ($baris) => $baris['nama'] !== '')
            ->values()
            ->all();

        $tercatat = (int) ($this->data['jumlah_peserta'] ?? 0);

        $pesan = count($bersih) === $tercatat
            ? 'Daftar peserta tersimpan.'
            : count($bersih).' nama tersimpan untuk '.$tercatat.' peserta yang tercatat.';

        /*
         | Dikirim sebagai PATCH, bukan lewat kirimData().
         |
         | Penolong itu memakai POST — bentuk yang dipakai formulir Orcha lain
         | karena harus membawa berkas gambar. Jalur peserta ini menerima PATCH,
         | dan POST ke sana dijawab 405. Uji yang menangkapnya sebelum sempat
         | sampai ke admin.
         |
         | Pemberitahuan suksesnya tetap mengikuti pola yang sama: tampil dulu di
         | halaman ini, baru berpindah balik ke detail setelah popupnya menutup.
         */
        $sebelum = count($this->data['riwayat_penggantian'] ?? []);

        try {
            $balasan = $this->orcha()->ubah("/pendaftaran/{$this->pendaftaranId}/peserta",
                ['peserta' => $bersih]);

            cache()->forget('orcha.perlu-ditindak');

            /*
             | Penggantian yang baru tercatat disebut di pemberitahuannya.
             |
             | Mengganti peserta di sini tidak terasa seperti "mengganti peserta"
             | — admin hanya mengetik nama lain lalu menyimpan. Tanpa kalimat ini
             | ia tidak pernah tahu bahwa kejadiannya dicatat, apalagi bahwa surat
             | pernyataannya bisa diunduh.
             */
            $penggantianBaru = collect(data_get($balasan, 'data.riwayat_penggantian', []))
                ->slice($sebelum)
                ->map(fn ($satu) => ($satu['dari'] ?? '—').' → '.($satu['ke'] ?? 'tanpa pengganti'))
                ->all();

            if ($penggantianBaru !== []) {
                $pesan .= ' Perubahan nama dicatat: '.implode(', ', $penggantianBaru)
                    .'. Surat pernyataannya bisa diunduh di halaman detail.';
            }

            $this->dispatch(
                'orcha-sukses-pindah',
                message: $pesan,
                url: route('admin.orcha.pendaftaran.detail', $this->pendaftaranId),
            );
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /** @return array{nama: string, titik_jemput: string, gantikan: ?string, gantikan_titik: ?string} */
    private function barisKosong(): array
    {
        return ['nama' => '', 'titik_jemput' => '', 'gantikan' => null, 'gantikan_titik' => null];
    }

    /**
     * Menandai satu baris sedang diganti orangnya.
     *
     * Nama lamanya disimpan lalu isian namanya dikosongkan, sehingga admin
     * mengetik nama pengganti di kotak yang bersih — bukan menimpa nama orang
     * lain di kotak yang sama. Kotak yang sama untuk dua orang berbeda itu yang
     * membuat admin ragu apakah ia sedang membetulkan ejaan atau mencoret
     * seseorang.
     */
    public function mulaiGanti(int $urutan): void
    {
        $lama = trim($this->barisPeserta[$urutan]['nama'] ?? '');

        if ($lama === '') {
            return;
        }

        $this->barisPeserta[$urutan]['gantikan'] = $lama;
        $this->barisPeserta[$urutan]['nama'] = '';

        /*
         | Titik jemputnya disimpan, tetapi TIDAK dikosongkan.
         |
         | Pengganti hampir selalu naik di titik yang sama — dikosongkan berarti
         | admin memilih ulang hal yang sama di setiap penggantian, dan yang
         | lupa memilih menghasilkan peserta tanpa titik jemput. Titik lamanya
         | baru tampil sebagai yang dicoret saat admin benar-benar memilih titik
         | lain.
         */
        $this->barisPeserta[$urutan]['gantikan_titik'] =
            trim($this->barisPeserta[$urutan]['titik_jemput'] ?? '') ?: null;
    }

    /** Batal mengganti: nama lamanya dikembalikan apa adanya. */
    public function batalGanti(int $urutan): void
    {
        $lama = $this->barisPeserta[$urutan]['gantikan'] ?? null;

        if ($lama === null) {
            return;
        }

        $this->barisPeserta[$urutan]['nama'] = $lama;
        $this->barisPeserta[$urutan]['gantikan'] = null;

        if (filled($this->barisPeserta[$urutan]['gantikan_titik'] ?? null)) {
            $this->barisPeserta[$urutan]['titik_jemput'] = $this->barisPeserta[$urutan]['gantikan_titik'];
        }

        $this->barisPeserta[$urutan]['gantikan_titik'] = null;
    }

    /**
     * @param  array<int, string>  $mentah
     * @param  bool  $dariBerkas  selnya sudah terpisah sejak dibaca, jadi hanya tab yang memisah
     * @return array<int, array{nama: string, titik_jemput: string, gantikan: ?string}>
     */
    private function uraikan(array $mentah, bool $dariBerkas = false): array
    {
        return collect($mentah)
            ->map(function ($baris) use ($dariBerkas) {
                /*
                 | Tempelan dipisah tab, titik koma, atau koma — tiga bentuk yang
                 | sama-sama datang ke admin.
                 |
                 | Berkas TIDAK. Sel-selnya sudah terpisah sejak dibaca lalu
                 | disambung dengan tab di sini, jadi memisah ulang dengan koma
                 | hanya merusak isi selnya sendiri: "Budi Santoso, S.Pd" terbaca
                 | sebagai nama "Budi Santoso" dengan titik jemput " S.Pd", dan
                 | gelar di belakang nama bukan hal yang jarang di daftar peserta.
                 */
                $pemisah = $dariBerkas ? '/\t/' : '/\t|;|,/';

                $bagian = preg_split($pemisah, (string) $baris);
                $nama = trim((string) ($bagian[0] ?? ''));

                /*
                 | Penggantian boleh dinyatakan langsung di tempelan maupun berkas,
                 | memakai tanda panah: "Haha > Wiam".
                 |
                 | Panitia mengirim daftarnya sekaligus — sebagian nama baru,
                 | sebagian menggantikan yang berhalangan — dan memaksa admin
                 | memilah dua kelompok itu dengan tangan hanya memindahkan
                 | pekerjaan, tidak menghilangkannya.
                 */
                $gantikan = null;

                if (preg_match('/^(.+?)\s*(?:->|>|=>)\s*(.+)$/u', $nama, $cocok)) {
                    $gantikan = trim($cocok[1]);
                    $nama = trim($cocok[2]);
                }

                // Berkas boleh menyatakannya lewat kolom ketiga: "Menggantikan".
                if ($dariBerkas && filled($bagian[2] ?? null)) {
                    $gantikan = trim((string) $bagian[2]);
                }

                // Penomoran daftar WhatsApp ikut terbuang: "1." "2)" "3 -".
                $nama = trim(preg_replace('/^\s*\d+\s*[.)\-]?\s*/', '', $nama));

                return [
                    'nama' => $nama,
                    'titik_jemput' => trim((string) ($bagian[1] ?? '')),
                    'gantikan' => $gantikan,
                    'gantikan_titik' => null,
                ];
            })
            ->filter(fn ($baris) => $baris['nama'] !== '')
            // Baris judul dari Excel ("Nama", "Nama Peserta") tidak ikut jadi peserta.
            ->reject(fn ($baris) => in_array(mb_strtolower($baris['nama']), ['nama', 'nama peserta', 'peserta'], true))
            ->values()
            ->all();
    }

    /**
     * Hasil uraian menimpa baris kosong, dan menambah yang sudah terisi.
     *
     * @param  array<int, array{nama: string, titik_jemput: string}>  $baru
     */
    private function gabungkan(array $baru): void
    {
        $daftar = collect($this->barisPeserta)
            ->filter(fn ($baris) => trim($baris['nama'] ?? '') !== '')
            ->values()
            ->all();

        foreach ($baru as $masuk) {
            /*
             | Nama yang menyatakan menggantikan seseorang MENIMPA barisnya,
             | bukan menambah baris baru. Kalau ditambahkan, rombongan berisi
             | dua orang untuk satu kursi — yang digantikan tetap terdaftar, dan
             | jumlah pesertanya melar melebihi yang dibayar.
             */
            $urutan = $masuk['gantikan'] === null ? false : collect($daftar)->search(
                fn ($baris) => mb_strtolower(trim($baris['nama'] ?? '')) === mb_strtolower($masuk['gantikan'])
            );

            if ($urutan === false) {
                $daftar[] = $masuk;

                continue;
            }

            $titikLama = trim((string) ($daftar[$urutan]['titik_jemput'] ?? ''));
            $titikBaru = $masuk['titik_jemput'] ?: $titikLama;

            $daftar[$urutan] = [
                'nama' => $masuk['nama'],
                'titik_jemput' => $titikBaru,
                'gantikan' => $masuk['gantikan'],
                'gantikan_titik' => $titikLama ?: null,
            ];
        }

        $this->barisPeserta = array_values($daftar);
    }

    /**
     * Titik jemput yang boleh dipilih.
     *
     * Diambil dari tiga sumber, digabung: titik yang ditawarkan paketnya, titik
     * rombongan pada pendaftaran ini, dan titik yang SUDAH dipakai peserta di
     * daftar. Sumber ketiga itu yang menjaga data lama — pendaftaran yang
     * terlanjur menyimpan ejaan di luar daftar tetap punya pilihannya sendiri,
     * jadi menyimpan ulang tidak diam-diam memindahkan orang ke titik lain.
     *
     * Ejaan yang sama beda huruf besar-kecil dianggap satu; yang pertama muncul
     * yang dipakai.
     *
     * @return array<int, string>
     */
    public function pilihanTitik(): array
    {
        $dariPaket = collect(data_get($this->data, 'paket.titik_jemput') ?? []);

        $dariRombongan = collect(preg_split('/[,;\/\n]+/', (string) ($this->data['titik_jemput'] ?? '')));

        $dariBaris = collect($this->barisPeserta)->pluck('titik_jemput');

        return $dariPaket
            ->merge($dariRombongan)
            ->merge($dariBaris)
            ->map(fn ($titik) => trim((string) $titik))
            ->filter()
            ->unique(fn ($titik) => mb_strtolower($titik))
            ->values()
            ->all();
    }

    /**
     * Nama peserta yang riwayat kesehatannya sudah masuk.
     *
     * Kaitan antara peserta dan riwayat kesehatannya berdasarkan NAMA, bukan
     * nomor. Karena itu mengganti ejaan nama seseorang yang sudah mengisi akan
     * memutus kaitan itu — riwayatnya tidak hilang, tetapi tidak lagi dikenali
     * sebagai miliknya, dan manifes mencetaknya sebagai peserta yang belum
     * mengisi. Ditandai di layar supaya admin tahu sebelum mengetik, bukan
     * setelahnya.
     *
     * @return array<int, string> nama dalam huruf kecil
     */
    public function namaSudahIsi(): array
    {
        $belum = collect($this->data['peserta_belum_isi'] ?? [])
            ->map(fn ($nama) => mb_strtolower(trim($nama)))
            ->all();

        return collect($this->data['peserta'] ?? [])
            ->pluck('nama')
            ->map(fn ($nama) => mb_strtolower(trim((string) $nama)))
            ->filter()
            ->reject(fn ($nama) => in_array($nama, $belum, true))
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.pendaftaran.peserta', [
            'pendaftaran' => $this->data,
            'sudahIsi' => $this->namaSudahIsi(),
            'pilihanTitik' => $this->pilihanTitik(),
        ])->layout('livewire.layout.templateindex');
    }
}
