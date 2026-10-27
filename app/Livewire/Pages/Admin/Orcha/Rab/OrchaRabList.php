<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Daftar RAB & itinerary private trip, sekaligus pintu membuat yang baru.
 *
 * Membuat RAB hanya menanyakan yang dibutuhkan untuk mulai menghitung —
 * siapa, ke mana, berapa hari, berapa orang. Sisanya disusun di halaman
 * penyusun, tempat admin mengklik destinasi dan melihat harganya bergerak.
 */
class OrchaRabList extends Component
{
    use MemanggilOrcha;

    public bool $tambah = false;

    public array $isian = [];

    /*
     | $filterStatus sudah milik trait MemanggilOrcha — tidak dideklarasikan
     | ulang di sini supaya atribut #[Url]-nya tidak tertimpa diam-diam.
     */
    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    #[Url(as: 'jenis', except: '')]
    public string $filterJenis = '';

    /** Hanya yang lewat masa berlaku dan masih menunggu jawaban. */
    #[Url(as: 'basi', except: false)]
    public bool $filterKedaluwarsa = false;

    #[Url(as: 'urut', except: '')]
    public string $urut = '';

    #[Url(as: 'baris', except: 10)]
    public int $barisHalaman = 10;

    /** Panel salin penawaran; berisi id RAB yang sedang disalin. */
    public ?int $salinDari = null;

    public array $salinIsian = [];

    /** Riwayat versi penawaran yang sedang dibuka. */
    public ?int $versiUntuk = null;

    public array $versi = [];

    public array $versiMeta = [];

    private function kosongkan(): void
    {
        $this->isian = [
            'judul' => '',
            'nama_pelanggan' => '',
            'whatsapp' => '',
            'provinsi' => '',
            'daerah' => '',
            'tanggal_mulai' => '',
            // Diturunkan dari tanggal mulai + jumlah hari; tidak dikirim ke
            // Orcha, karena di sana pun ia diturunkan, bukan disimpan.
            'tanggal_pulang' => '',
            'jumlah_hari' => 3,
            'jumlah_malam' => 2,
            'jumlah_peserta' => 10,
            /*
             | Private trip sebagai bawaan: jumlah orangnya sudah pasti di
             | kesepakatan, jadi kursi kosong bukan risiko kantor. Open trip
             | dipilih sendiri, karena ia menuntut keputusan lain — jumlah
             | paling rendah yang masih mau diberangkatkan.
             */
            'jenis' => 'private',
            'peserta_minimum' => 5,
            'harga_dasar' => 'minimum',
        ];
    }

    public function mount(): void
    {
        $this->kosongkan();
    }

    public function bukaTambah(): void
    {
        $this->tambah = true;
        $this->kosongkan();
        $this->resetValidation();
    }

    public function tutup(): void
    {
        $this->tambah = false;
        $this->kosongkan();
    }

    /**
     * Judul yang dirakit dari isian lain: "Private Trip Yogyakarta 3H2M".
     *
     * Judul adalah satu-satunya isian wajib yang jawabannya sudah diketahui
     * dari kotak-kotak di sebelahnya. Mengetiknya ulang bukan keputusan, cuma
     * penyalinan — dan penyalinan yang dilakukan berpuluh kali sehari adalah
     * tempat salah ketik lahir.
     *
     * Tetap bisa ditulis sendiri; yang dihapus kewajibannya, bukan pilihannya.
     */
    /** Open trip menuntut isian yang tidak dipakai private trip. */
    public function openTrip(): bool
    {
        return ($this->isian['jenis'] ?? 'private') === 'open';
    }

    public function judulOtomatis(): string
    {
        $awalan = $this->openTrip() ? 'Open Trip ' : 'Private Trip ';
        $tujuan = trim((string) ($this->isian['daerah'] ?? '')) ?: trim((string) ($this->isian['provinsi'] ?? ''));
        $hari = max(1, (int) ($this->isian['jumlah_hari'] ?? 1));
        $malam = max(0, (int) ($this->isian['jumlah_malam'] ?? 0));

        return trim($awalan.$tujuan.' '.$hari.'H'.$malam.'M');
    }

    /**
     * Malam mengikuti hari: 3 hari hampir selalu 2 malam. Admin yang ingin
     * lain tinggal mengubahnya, tetapi tidak perlu mengingat untuk mengubah.
     *
     * Tanggal pulang ikut bergeser supaya ketiga angka itu tidak pernah
     * saling bertentangan di layar.
     */
    public function updatedIsianJumlahHari($hari): void
    {
        $this->isian['jumlah_malam'] = max(0, (int) $hari - 1);
        $this->selaraskanTanggalPulang();
    }

    /**
     * Tanggal pulang diisi → jumlah hari & malam dihitung dari rentangnya.
     *
     * Yang disepakati dengan pelanggan tanggalnya ("berangkat 10, pulang
     * 12"), bukan jumlah harinya. Menyuruh admin menerjemahkannya sendiri
     * adalah hitungan kepala yang mudah meleset satu — dan yang meleset
     * satu hari menggeser seluruh modal akomodasi.
     */
    public function updatedIsianTanggalPulang(): void
    {
        $this->hitungDariTanggal();
    }

    public function updatedIsianTanggalMulai(): void
    {
        $this->hitungDariTanggal();
    }

    private function hitungDariTanggal(): void
    {
        $mulai = trim((string) ($this->isian['tanggal_mulai'] ?? ''));
        $pulang = trim((string) ($this->isian['tanggal_pulang'] ?? ''));

        if ($mulai === '' || $pulang === '') {
            return;
        }

        try {
            $a = \Carbon\Carbon::parse($mulai)->startOfDay();
            $b = \Carbon\Carbon::parse($pulang)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        // Pulang sebelum berangkat tidak dihitung — angkanya akan negatif,
        // dan menolaknya diam-diam lebih baik daripada menulis "0 hari".
        if ($b->lessThan($a)) {
            return;
        }

        // Hari keberangkatan ikut terhitung: 10 → 12 adalah 3 hari 2 malam.
        $this->isian['jumlah_hari'] = $a->diffInDays($b) + 1;
        $this->isian['jumlah_malam'] = $a->diffInDays($b);
    }

    /** Menggeser tanggal pulang mengikuti jumlah hari yang baru. */
    private function selaraskanTanggalPulang(): void
    {
        $mulai = trim((string) ($this->isian['tanggal_mulai'] ?? ''));

        if ($mulai === '') {
            return;
        }

        try {
            $this->isian['tanggal_pulang'] = \Carbon\Carbon::parse($mulai)
                ->addDays(max(1, (int) $this->isian['jumlah_hari']) - 1)
                ->toDateString();
        } catch (\Throwable) {
            // Tanggal yang belum selesai diketik bukan galat.
        }
    }

    public function simpan(): void
    {
        // Judul kosong berarti "pakai yang dirakit", bukan "belum diisi".
        $this->isian['judul'] = trim((string) $this->isian['judul']) ?: $this->judulOtomatis();

        $this->validate([
            'isian.judul' => 'required|string|min:3|max:150',
            'isian.nama_pelanggan' => 'required|string|min:2|max:120',
            'isian.whatsapp' => 'nullable|string|max:32',
            'isian.provinsi' => 'required|string|max:80',
            'isian.daerah' => 'nullable|string|max:80',
            'isian.tanggal_mulai' => 'nullable|date',
            'isian.jumlah_hari' => 'required|integer|min:1|max:30',
            'isian.jumlah_malam' => 'required|integer|min:0|max:30|lte:isian.jumlah_hari',
            'isian.jumlah_peserta' => 'required|integer|min:1|max:500',
            // Peserta minimum hanya berarti pada open trip, dan tidak boleh
            // melebihi targetnya sendiri.
            'isian.peserta_minimum' => $this->openTrip()
                ? 'required|integer|min:1|max:500|lte:isian.jumlah_peserta'
                : 'nullable',
        ], [
            'isian.jumlah_malam.lte' => 'Jumlah malam tidak boleh melebihi jumlah hari.',
            'isian.peserta_minimum.lte' => 'Peserta minimum tidak boleh lebih banyak daripada targetnya.',
        ], [
            'isian.judul' => 'judul', 'isian.nama_pelanggan' => 'nama pelanggan', 'isian.provinsi' => 'provinsi',
            'isian.jumlah_hari' => 'jumlah hari', 'isian.jumlah_peserta' => 'jumlah peserta',
            'isian.peserta_minimum' => 'peserta minimum',
        ]);

        // Private trip tidak membawa isian open trip sama sekali: kolom yang
        // terisi tapi tak berarti akan dibaca sebagai keadaan nyata kelak.
        if (! $this->openTrip()) {
            $this->isian['peserta_minimum'] = null;
            $this->isian['harga_dasar'] = 'peserta';
        }

        /*
         | tanggal_pulang TIDAK ikut dikirim.
         |
         | Di Orcha ia diturunkan dari tanggal mulai + jumlah hari, bukan
         | disimpan. Mengirimnya berarti menitipkan angka kedua untuk hal
         | yang sama — dan yang kedua itulah yang akan berselisih begitu
         | jumlah harinya diubah lewat jalur lain.
         */
        $data = array_map(fn ($v) => is_string($v) ? (trim($v) ?: null) : $v,
            collect($this->isian)->except('tanggal_pulang')->all()) + [
                // Titik awal yang masuk akal; diatur ulang di halaman penyusun.
                'margin_jenis' => 'persen',
                'margin_nilai' => 20,
            ];

        try {
            $baru = $this->orcha()->kirim('/rab', $data)['data'] ?? null;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        $this->dispatch('orcha-sukses-pindah',
            message: 'RAB '.($baru['kode'] ?? '').' dibuat. Sekarang pilih destinasinya.',
            url: route('admin.orcha.rab.susun', $baru['id']));
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/rab/{$id}", 'RAB dihapus.');
    }

    public function updatedFilterProvinsi(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterJenis(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterKedaluwarsa(): void
    {
        $this->halaman = 1;
    }

    public function updatedUrut(): void
    {
        $this->halaman = 1;
    }

    public function updatedBarisHalaman(): void
    {
        $this->halaman = 1;
    }

    /** Dibatasi ke daftar yang dikenal: angkanya datang dari URL. */
    protected function perHalaman(): int
    {
        return in_array($this->barisHalaman, [10, 25, 50, 100], true) ? $this->barisHalaman : 10;
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterStatus = '';
        $this->filterProvinsi = '';
        $this->filterJenis = '';
        $this->filterKedaluwarsa = false;
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== ''
            || $this->filterProvinsi !== '' || $this->filterJenis !== ''
            || $this->filterKedaluwarsa;
    }

    /**
     * Ubin ringkasan: memasang saringannya, menekan lagi melepasnya.
     *
     * Keputusannya dirakit DI SINI, bukan di dalam atribut wire:. Isi
     * atribut bukan kode PHP — ekspresi bersarang di sana sampai ke
     * peramban apa adanya dan kliknya mati tanpa satu pun pesan.
     * Dijaga WireAtributMentahTest.
     */
    public function pasangUbin(string $kunci): void
    {
        $this->halaman = 1;

        if ($kunci === 'kedaluwarsa') {
            $this->filterKedaluwarsa = ! $this->filterKedaluwarsa;

            return;
        }

        $this->filterStatus = $this->filterStatus === $kunci ? '' : $kunci;
    }

    /** Ubin yang sedang memasang saringan — dipakai menandainya. */
    public function ubinNyala(string $kunci): bool
    {
        return $kunci === 'kedaluwarsa'
            ? $this->filterKedaluwarsa
            : $this->filterStatus === $kunci;
    }

    /**
     * Warna untung menurut tipisnya margin.
     *
     * Sebelumnya kolom ini selalu hijau — penawaran yang merugi pun
     * tergambar meyakinkan. Ia satu-satunya angka di layar yang bisa
     * mencegah penawaran merugi terkirim, dan warnanya tidak pernah
     * berubah.
     */
    public function rupaUntung(?float $persen): string
    {
        if ($persen === null) {
            return 'netral';
        }

        return match (true) {
            $persen < 0 => 'rugi',
            $persen < 10 => 'tipis',
            default => 'sehat',
        };
    }

    /**
     * Tautan WhatsApp berisi pesan yang sudah terketik.
     *
     * Nomornya tersimpan dan PDF-nya sudah ada, tetapi mengirimnya tetap
     * manual: unduh, buka WA, cari nomor, ketik pesan — di SETIAP
     * penawaran. Nomornya dinormalkan ke format internasional karena
     * wa.me menolak "0812...".
     */
    public function tautanWa(array $baris): ?string
    {
        $angka = preg_replace('/\D/', '', (string) ($baris['whatsapp'] ?? ''));

        if (strlen($angka) < 8) {
            return null;
        }

        if (str_starts_with($angka, '0')) {
            $angka = '62'.ltrim($angka, '0');
        }

        $pesan = 'Halo'.($baris['nama_pelanggan'] ? ' '.$baris['nama_pelanggan'] : '')
            .', berikut penawaran '.($baris['judul'] ?: 'perjalanan').' ('.$baris['kode'].'). '
            .'Harga '.$baris['harga_per_orang_teks'].' per orang untuk '.$baris['jumlah_peserta'].' peserta. '
            .'Berkas rinciannya menyusul ya.';

        return 'https://wa.me/'.$angka.'?text='.rawurlencode($pesan);
    }

    public function bukaSalin(int $id, array $baris): void
    {
        $this->salinDari = $id;
        $this->salinIsian = [
            'nama_pelanggan' => '',
            'whatsapp' => '',
            'tanggal_mulai' => '',
        ];
        $this->resetValidation('salinIsian.nama_pelanggan');
    }

    public function tutupSalin(): void
    {
        $this->salinDari = null;
        $this->salinIsian = [];
    }

    /** Menyalin penawaran; yang dikosongkan ikut nilai aslinya. */
    public function salin(): void
    {
        if ($this->salinDari === null) {
            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/rab/{$this->salinDari}/salin", array_filter([
                'nama_pelanggan' => trim((string) ($this->salinIsian['nama_pelanggan'] ?? '')) ?: null,
                'whatsapp' => trim((string) ($this->salinIsian['whatsapp'] ?? '')) ?: null,
                'tanggal_mulai' => ($this->salinIsian['tanggal_mulai'] ?? '') ?: null,
            ], fn ($n) => $n !== null));

            $this->tutupSalin();
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Penawaran disalin.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->addError('salinIsian.nama_pelanggan', $e->getMessage());
        }
    }

    /** Versi yang pernah dikirim ke pelanggan. */
    public function bukaVersi(int $id): void
    {
        try {
            $balasan = $this->orcha()->ambil("/rab/{$id}/versi");

            $this->versiUntuk = $id;
            $this->versi = $balasan['data'] ?? [];
            $this->versiMeta = $balasan['meta'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupVersi(): void
    {
        $this->versiUntuk = null;
        $this->versi = [];
        $this->versiMeta = [];
    }

    public function render()
    {
        $hasil = $this->muat('/rab', $this->parameterDaftar([
            'provinsi' => $this->filterProvinsi,
            'jenis' => $this->filterJenis,
            'kedaluwarsa' => $this->filterKedaluwarsa ? 1 : '',
            'urut' => $this->urut,
        ]));

        return view('livewire.pages.admin.orcha.rab.daftar', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            // Dihitung Orcha dari SELURUH tabel, bukan halaman ini.
            'ringkas' => $hasil['meta']['ringkas'] ?? [],
            'status' => $hasil['meta']['kosakata']['status'] ?? [],
            // Dipakai saringan DAN formulir tambah, jadi selalu dimuat —
            // tetapi lewat simpanan, bukan satu panggilan tiap gambar ulang.
            'provinsi' => $this->muatKatalog()['provinsi'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }

    /** Provinsi & destinasi; disimpan sebentar karena jarang berubah. */
    private function muatKatalog(): array
    {
        $simpan = cache()->get('orcha.rab.katalog');

        if (is_array($simpan) && $simpan !== []) {
            return $simpan;
        }

        try {
            $data = $this->orcha()->ambil('/rab/katalog')['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }

        if ($data !== []) {
            cache()->put('orcha.rab.katalog', $data, now()->addMinutes(10));
        }

        return $data;
    }
}
